<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Wallet;
use App\Models\Transaction;
use Illuminate\Http\Request;

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

        if ($id == 0) {
            $wallets = $wallets_all;
            $not_found_error_message = __('No wallets exist.');
        }
        else {
            $wallets = Wallet::where(['id' => $id, 'user_id' => $userid])->get();
            $not_found_error_message = __('The wallet does not exist.');
        }

        if ($wallets === null) {
            return back()->withErrors($not_found_error_message);
        }

        $transactions = [];

        /*
         * Get all transactions from all wallets, or single wallet. Get only transactions that user is a sender or
         * receiver. And only those who user has not deleted.
         */
        foreach ($wallets as $wallet) {
            $transactions = array_merge($transactions, Transaction::where(
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
                })
                ->where(
                    function ($query) use ($userid) {
                        $query
                            ->where('deleted_by', '<>', $userid)
                            ->orWhere('deleted_by', null);
                    }
                )
                ->get()
                ->toArray()
            );
        }

        $totals = [
            'in' => 0,
            'out' => 0
        ];

        // Pre-process transactions before displaying and calculate the totals.
        if ($transactions) {
            foreach ($transactions as &$transaction) {
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
                        $totals['in'] += $transaction['amount'];
                    }
                    elseif (strpos($transaction['to'], '-') !== false) {
                        // User transfered money from wallet to another credit card. This is an outgoing transaction.
                        $totals['out'] += $transaction['amount'];
                    }
                }
                // Transactions between wallets.
                else {
                    foreach ($wallets as $wallet) {
                        if ($wallet->uniqid === $transaction['from']) {
                            $totals['out'] += $transaction['amount'];
                        }
                        elseif ($wallet->uniqid === $transaction['to']) {
                            $totals['in'] += $transaction['amount'];
                        }
                    }
                }
            }
        }
        unset($transaction);

        $totals['total'] = $totals['in'] - $totals['out'];

        switch ($param) {
            case 'in':
                $totals['in'] = '+'.$totals['in'];
                $totals['style'] = 'text-success';
                break;

            case 'out':
                $totals['out'] = '-'.$totals['out'];
                $totals['style'] = 'text-danger';
                break;

            default:
                $totals['in'] = '+'.$totals['in'];
                $totals['out'] = '-'.$totals['out'];

                if ($totals['total'] > 0) {
                    $totals['style'] = 'text-success';
                }
                else {
                    $totals['total'] = '-'.$totals['total'];
                    $totals['style'] = 'text-danger';
                }
        }

        return view('pages.transactions', compact('wallets_all', 'wallets', 'transactions', 'totals'))
            ->with('info', __('No transactions found.'));
    }

    public function add()
    {
        return 'show create transaction form';
    }

    public function store() // create a transaction
    {
        
    }

    public function destroy() // delete a transaction
    {

    }

    public function fraudulent() // mark transaction as fraudulent or back
    {

    }
}
