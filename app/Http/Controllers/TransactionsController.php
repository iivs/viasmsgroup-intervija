<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Wallet;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TransactionsController extends Controller
{
    /**
     * Show list of transactions. All, incoming, outgoing, from all or single wallet, and calculate the totals.
     * If a specific wallet is select and it's not found, return with error message. If not wallets exist and user is
     * accessing transactions, redirect back and show a different error message.
     *
     * @param Illuminate\Http\Request $request
     * @param  int      $id Wallet ID.
     *                      Possible values:
     *                          null|0 - show transactions from all wallets;
     *                          <int>   - show transaction from a single wallet.
     * @param  string   $id Determines what type of transactions to display incoming or outgoing.
     *                      Possible values:
     *                          null  - show both incoming and outgoing transactions;
     *                          'in'  - show only incoming transactions;
     *                          'out' - show only outgoing transactions.
     *
     * @return Illuminate\View\View|Illuminate\Http\RedirectResponse
     */
    public function index(Request $request, $id = 0, $param = null)
    {
        $userid = Auth::user()->id;

        // Get all user wallets for dropdown and in case no specific wallet was selected.
        $wallets_all = Wallet::where('user_id', $userid)->get();

        if (!$wallets_all->toArray()) {
            return back()->withErrors(__('No wallets exist.'));
        }

        if ($id == 0) {
            $wallets = $wallets_all;
        } else {
            $wallets = Wallet::where(['id' => $id, 'user_id' => $userid])->get();
        }

        if (!$wallets->toArray()) {
            return back()->withErrors(__('The wallet does not exist.'));
        }

        $all_transactions = [];

        /*
         * Get all transactions from all wallets, or single wallet. Get only transactions that user is a sender or
         * receiver. And only those who user has not deleted.
         */
        foreach ($wallets as $wallet) {
            $transactions = Transaction::where(
                function ($query) use ($wallet, $param) {
                    switch ($param) {
                        case 'out':
                            $query->where('from', $wallet->uniqid);
                            break;
                        case 'in':
                            $query->where('to', $wallet->uniqid);
                            break;
                        default:
                            $query
                                ->where('from', $wallet->uniqid)
                                ->orWhere('to', $wallet->uniqid);
                    }
                }
            )
            ->where(
                function ($query) use ($userid) {
                    $query->where('user_id', $userid);
                }
            )
            ->get()
            ->toArray();

            $all_transactions = array_merge($all_transactions, $transactions);
        }

        $totals = [
            'in' => 0,
            'out' => 0,
            // IDs of transactions that have been processed.
            'processed' => []
        ];

        // Pre-process transactions before displaying and calculate the totals.
        if ($all_transactions) {
            foreach ($all_transactions as &$transaction) {
                // Update date to human readable format without timezones.
                $transaction['created_at'] = date('Y-m-d H:i:s', strtotime($transaction['created_at']));

                $transaction['is_fraudulent']
                    = ($transaction['is_fraudulent'] == \Config::get('transactions.is_fraudulent.yes'))
                    ? __('Yes')
                    : '';

                // Hide the full card number and calculate incoming and outgoing totals.
                if ($transaction['type'] == \Config::get('transactions.type.card')) {
                    if (strpos($transaction['from'], '-') !== false) {
                        // Some one transfered money from a credit card to wallet. This is an incomming transaction.
                        if (!array_key_exists($transaction['id'], $totals['processed'])) {
                            $totals['in'] += $transaction['amount'];
                        }
                        
                    } elseif (strpos($transaction['to'], '-') !== false) {
                        // User transfered money from wallet to another credit card. This is an outgoing transaction.
                        if (!array_key_exists($transaction['id'], $totals['processed'])) {
                            $totals['out'] -= $transaction['amount'];
                        }
                    }
                } else {
                    // Transactions between wallets.
                    foreach ($wallets as $wallet) {
                        if ($transaction['from'] === $wallet->uniqid) {
                            if (!array_key_exists($transaction['id'], $totals['processed'])) {
                                $totals['out'] -= $transaction['amount'];
                            }
                        }
                        if ($transaction['to'] === $wallet->uniqid) {
                            if (!array_key_exists($transaction['id'], $totals['processed'])) {
                                $totals['in'] += $transaction['amount'];
                            }
                        }
                    }
                }

                $totals['processed'][$transaction['id']] = true;
            }
        }
        unset($transaction);

        switch ($param) {
            case 'in':
                $totals['total'] = $totals['in'];
                $totals['style'] = 'text-success';
                break;
            case 'out':
                if ($totals['out'] == 0) {
                    $totals['style'] = 'text-success';
                } else {
                    $totals['style'] = 'text-danger';
                }

                $totals['total'] = $totals['out'];
                break;
            default:
                $totals['total'] = $totals['in'] + $totals['out'];
                $totals['in'] = '+'.$totals['in'];

                if ($totals['total'] > 0 || $totals['total'] == 0) {
                    $totals['style'] = 'text-success';
                } elseif ($totals['total'] < 0) {
                    $totals['style'] = 'text-danger';
                }
        }

        return view('pages.transactions', compact('wallets_all', 'wallets', 'all_transactions', 'totals'));
    }

    /**
     * Display the transaction create form.
     *
     * @return Illuminate\View\View
     */
    public function add($id = null)
    {
        $userid = Auth::user()->id;

        // Get all user wallets for dropdown and in case no specific wallet was selected.
        $wallets_all = Wallet::where('user_id', $userid)->get();

        if (!$wallets_all->toArray()) {
            return back()->withErrors(__('No wallets exist.'));
        }

        return view('pages.transaction_add', compact('wallets_all'));
    }

    /**
     * Perform creation of a new transaction.
     * 
     * @param  Illuminate\Http\Request $request
     *
     * @return Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Create a custom validator to check the sender transaction type.
        $validator = Validator::make($request->all(), [
            'type_from' => 'required|integer|in:'.implode(',', \Config::get('transactions.type'))
        ], [
            'type_from.required' => __('Transaction type of sender is not specified.'),
            'type_from.in' => __('Invalid sender transaction type.')
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();

            return back()
                ->withErrors($errors)
                ->withInput($request->all());
        }

        /*
         * Sender transaction type was valid, but the other fields depend on the value. If user is sending money from
         * wallet destination can be a wallet or credit card. Senders wallet ID must exist. If user is sending money
         * from credit card, the destination can only be a wallet. Sender must provide card holders name, card number,
         * valid expirary date and security code.
         */
        switch ($request->input('type_from')) {
            case \Config::get('transactions.type.wallet'):
                $rules = [
                    'type_to' => 'required|integer|in:'.implode(',', \Config::get('transactions.type')),
                    'wallet_from' => 'required|integer'
                ];
                break;
            case \Config::get('transactions.type.card'):
                $rules = [
                    'type_to' => 'required|integer|in:'.\Config::get('transactions.type.wallet'),
                    'holder_from' => 'required',
                    'card_from' => 'required',
                    'date_m' => 'required|date_format:m',
                    'date_y' => 'required|date_format:y',
                    'cvc' => 'required|numeric'
                ];
                break;
        }

        // Additionally the amount is also a mandatory field.
        $rules += [
            'amount' => 'required'
        ];

        /*
         * If user is sending money to a wallet as destination, receiver wallet ID must exist. If user is sending money
         * from wallet to a credit card, user must provide receiver card holder name and card number.
         */
        switch ($request->input('type_to')) {
            case \Config::get('transactions.type.wallet'):
                $rules += [
                    'wallet_to' => 'required|string'
                ];
                break;
            case \Config::get('transactions.type.card'):
                $rules += [
                    'holder_to' => 'required|string',
                    'card_to' => 'required|string'
                ];
                break;
        }

        // Add rules to validation and set custom error messages.
        $validator = Validator::make($request->all(), $rules, [
            'type_to.required' => __('Transaction type of receiver is not specified.'),
            'type_to.in' => __('Invalid receiver transaction type.'),
            'wallet_from.required' => __("Sender's wallet not selected."),
            'wallet_to.required' => __("Receiver's wallet is not specified."),
            'amount.required' => __('Invalid transaction amount.'),
            'holder_to.required' => __("Receiver's card holder name is not specified."),
            'holder_from.required' => __("Sender's card holder name is not specified."),
            'card_from.required' => __("Sender's credit card is not specified."),
            'card_to.required' => __("Receiver's credit card is not specified."),
            'date_m.required' => __("Sender's credit card month not specified."),
            'date_m.date_format' => __("Invalid sender's credit card month."),
            'date_y.required' => __("Sender's credit card year not specified."),
            'date_y.date_format' => __("Invalid sender's credit card year."),
            'cvc.required' => __('Invalid credit card security code.')
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();

            return back()
                ->withErrors($errors)
                ->withInput($request->all());
        }

        $userid = Auth::user()->id;

        /*
         * Once required fields and basic information is validated, validate if sender's wallet ID exists or given
         * card credentials are valid.
         */
        switch ($request->input('type_from')) {
            case \Config::get('transactions.type.wallet'):
                $wallet_from = Wallet::where(['id' => $request->input('wallet_from'), 'user_id' => $userid])->first();

                if ($wallet_from === null) {
                    return back()
                        ->withErrors(__('The wallet does not exist.'))
                        ->withInput($request->all());
                }

                // Check if given amount is actually an amount and not 0 or less.
                if ($request->input('amount') <= 0) {
                    return back()
                        ->withErrors(__('Invalid transaction amount.'))
                        ->withInput($request->all());
                }

                // Check if valid decimal.
                if (!preg_match('/^\d+(?:\.\d{2})?$/', $request->input('amount'), $match)) {
                    return back()
                        ->withErrors(__('Invalid transaction amount.'))
                        ->withInput($request->all());
                }

                // Check if given amount is allowed to transfer.
                if ($request->input('amount') > $wallet_from->balance) {
                    return back()
                        ->withErrors(__('Insuffcient funds.'))
                        ->withInput($request->all());
                }
                break;
            case \Config::get('transactions.type.card'):
                // Validate credit card information.
                if (!$this->validate_card_number($request->input('card_from'))) {
                    return back()
                        ->withErrors(__('Invalid credit card number.'))
                        ->withInput($request->all());
                }

                if (!$this->validate_card_expiry_date($request->input('date_m'), $request->input('date_y'))) {
                    return back()
                        ->withErrors(__('Invalid credit expiry date.'))
                        ->withInput($request->all());
                }

                if (!preg_match('/[0-9]{3}/', $request->input('cvc'))) {
                    return back()
                        ->withErrors(__('Invalid credit security code.'))
                        ->withInput($request->all());
                }
                break;
        }

        // Validate the destination wallet or card credentials.
        switch ($request->input('type_to')) {
            case \Config::get('transactions.type.wallet'):
                $wallet_to = Wallet::where('uniqid', $request->input('wallet_to'))->first();

                if ($wallet_to === null) {
                    return back()
                        ->withErrors(__('The destination wallet does not exist.'))
                        ->withInput($request->all());
                }

                if (
                    $request->input('type_from') == \Config::get('transactions.type.wallet')
                    && $wallet_from->uniqid === $wallet_to->uniqid
                ) {
                    return back()
                        ->withErrors(__('Cannot make transaction to same wallet.'))
                        ->withInput($request->all());
                }
                break;
            case \Config::get('transactions.type.card'):
                if (!$this->validate_card_number($request->input('card_to'))) {
                    return back()
                        ->withErrors(__('Invalid credit card number.'))
                        ->withInput($request->all());
                }
                break;
        }

        // Validation passed, proceed with saving transaction to DB.
        $data = [];
        switch ($request->input('type_from')) {
            case \Config::get('transactions.type.wallet'):
                switch ($request->input('type_to')) {
                    case \Config::get('transactions.type.wallet'):
                        // From wallet to wallet.
                        $data['type'] = \Config::get('transactions.type.wallet');
                        // Sender user ID.
                        $data['user_id'] = $userid;
                        $data['from'] = $wallet_from->uniqid;
                        $data['to'] = $wallet_to->uniqid;
                        $data['amount'] = $request->input('amount');
                        $data['in_fraudulent'] = \Config::get('transactions.is_fraudulent.no');

                        $transaction = Transaction::create($data);

                        // Update source wallet balance.
                        $wallet_from->balance -= $data['amount'];
                        $wallet_from->update(['balance' => $wallet_from->balance]);

                        /*
                         * If sender and receiver is the same user, create only one transaction. Otherwise, create
                         * another record and link them together.
                         */
                        if ($userid != $wallet_to->user_id) {
                            $data['user_id'] = $wallet_to->user_id;
                            $data['parent_id'] = $transaction->id;
                            // Link both transactions.
                            $transaction = ($transaction && Transaction::create($data));
                        }

                        // Update destination wallet balance.
                        $wallet_to->balance += $data['amount'];
                        $wallet_to->update(['balance' => $wallet_to->balance]);
                        break;
                    case \Config::get('transactions.type.card'):
                        // From wallet to card.
                        $data['type'] = \Config::get('transactions.type.card');
                        // Sender user ID.
                        $data['user_id'] = $userid;
                        $data['from'] = $wallet_from->uniqid;
                        // Partially hide card number.
                        $data['to'] = '****-****-****-'.substr($request->input('card_to'), -4);
                        $data['amount'] = $request->input('amount');
                        $data['in_fraudulent'] = \Config::get('transactions.is_fraudulent.no');
                        $transaction = Transaction::create($data);

                        // Update source wallet balance.
                        $wallet_from->balance -= $data['amount'];
                        $wallet_from->update(['balance' => $wallet_from->balance]);
                        break;
                }
                break;
            case \Config::get('transactions.type.card'):
                $data['type'] = \Config::get('transactions.type.card');
                // From card to wallet. Destination should be wallet.
                $data['user_id'] = $wallet_to->user_id;
                // Partially hide card number.
                $data['from'] = '****-****-****-'.substr($request->input('card_from'), -4);
                $data['to'] = $wallet_to->uniqid;
                $data['amount'] = $request->input('amount');
                $data['in_fraudulent'] = \Config::get('transactions.is_fraudulent.no');
                $transaction = Transaction::create($data);

                // Update destination wallet balance.
                $wallet_to->balance += $data['amount'];
                $wallet_to->update(['balance' => $wallet_to->balance]);
                break;
        }

        if ($transaction) {
            return redirect()
                ->route('transactions.all')
                ->withSuccess(__('Transaction successfully created.'));
        }
    }

    /**
     * Delete the transaction for this user.
     *
     * @param  int  $id  Transaction ID.
     *
     * @return Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $userid = Auth::user()->id;
        $transaction = Transaction::where(['id' => $id, 'user_id' => $userid])->first();

        if ($transaction === null) {
            return back()
                ->withErrors(__('The transaction does not exist.'));
        }

        if (Transaction::where('id', $id)->delete()) {
            return back()
                ->withSuccess(__('Transaction successfully deleted.'));
        }
    }

    /**
     * Mark the transaction as fraudulet for this and the other user as well if this transaction is from wallet to
     * wallet.
     *
     * @param  int  $id  Transaction ID.
     *
     * @return Illuminate\Http\RedirectResponse
     */
    public function update($id)
    {
        $userid = Auth::user()->id;
        $transaction = Transaction::where(['id' => $id, 'user_id' => $userid])->first();

        if ($transaction === null) {
            return back()
                ->withErrors(__('The transaction does not exist.'));
        }

        // Toggle the status.
        $is_fraudulent = ($transaction->is_fraudulent == \Config::get('transactions.is_fraudulent.no'))
            ? \Config::get('transactions.is_fraudulent.yes')
            : \Config::get('transactions.is_fraudulent.no');

        $transaction->update(['is_fraudulent' => $is_fraudulent]);

        // Find related transaction and update the field.
        $transaction = Transaction::where('parent_id', $id)->first();
        $transaction->update(['is_fraudulent' => $is_fraudulent]);

        return back()
                ->withSuccess(__('Transaction status successfully updated.'));
    }

    /**
     * Validate syntax of typical credit card number.
     * 
     * @param  string $number  Credit card number.
     *
     * @return bool
     */
    private function validate_card_number(string $number): bool
    {
        return preg_match('/[0-9]{4}-[0-9]{4}-[0-9]{4}-[0-9]{4}/', $number);
    }

    /**
     * Validate card expiry date. Year cannot be less that current year. If this is the same year, month cannot be less
     * than current month.
     * 
     * @param  int $month  Given month.
     * @param  int $year  Given year.
     *
     * @return bool
     */
    private function validate_card_expiry_date(int $month, int $year): bool
    {
        if ($year > date('y')) {
            return true;
        } elseif ($year == date('y') && $month >= date('m')) {
            return true;
        }

        return false;
    }
}
