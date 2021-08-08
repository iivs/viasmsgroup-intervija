<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Tests\TestCase;
use App\Models\Wallet;
use App\Models\Transaction;

class TransactionTest extends TestCase
{
    /**
     * Default data for user registration.
     *
     * @var array
     */
    private $user_data = [
        'name' => 'John Doe',
        'email' => 'john@doe.com',
        'password' => 'password',
        'password_confirmation' => 'password'
    ];

    /**
     * Default transaction data for creating new transactions.
     *
     * @var array
     */
    private $transaction_data = [
        'holder_from' => 'John Doe',
        'card_from' => '1111-1111-1111-1111',
        'date_m' => '12',
        'date_y' => '23',
        'cvc' => '123',
        'amount' => '100'
    ];

    /**
     * Check if transaction list view has the necessary table for all wallets.
     */
    public function testTransactionListViewAll()
    {
        $this->createUser();
        $userid = auth()->user()->id;
        $wallets = Wallet::where('user_id', $userid)->get();

        $response = $this->get('/transactions');
        $response->assertStatus(Response::HTTP_OK);
        $response->assertSessionHasNoErrors();
        
        $view = $this->view('pages.transactions', [
            'wallets_all' => $wallets,
            'wallets' => $wallets,
            'all_transactions' => [],
            'totals' => [
                'in' => '+0',
                'out' => 0,
                'total' => 0,
                'style' => 'text-success'
            ]
        ]);
        $view->assertSee(__('My Wallets'));
        $view->assertSee(__('Transactions'));
        $view->assertSee(__('Log out'));

        $view->assertSee(__('Add transaction'));
        $view->assertSee(__('Select a wallet').':');
        $view->assertSee(__('All'));
        $view->assertSee(__('ID'));
        $view->assertSee(__('From'));
        $view->assertSee(__('To'));
        $view->assertSee(__('Amount').' €');
        $view->assertSee(__('Fraudulent'));
        $view->assertSee(__('Date'));
        $view->assertSee(__('Incoming').':');
        $view->assertSee(__('Outgoing').':');
        $view->assertSee(__('Total').':');
        $view->assertSee('0 €');
        $view->assertSee('+0 €');
    }

    /**
     * Check if transaction creation view has the necessary field. This view has some JS added, so it's dynamic and hard
     * to test. But all the blocks and fields should exist. Additionally add errors on page to check them out.
     */
    public function testTransactionAddView()
    {
        $this->createUser();
        $userid = auth()->user()->id;
        $wallets = Wallet::where('user_id', $userid)->get();

        $response = $this->get('/transaction');
        $response->assertStatus(Response::HTTP_OK);
        $response->assertSessionHasNoErrors();

        $view = $this->withViewErrors([
            __("Sender's card holder name is not specified."),
            __("Sender's credit card is not specified."),
            __("Sender's credit card month not specified."),
            __("Sender's credit card year not specified."),
            __('Invalid credit card security code.'),
            __('Invalid transaction amount.'),
            __("Receiver's wallet is not specified."),
            __("Receiver's card holder name is not specified."),
            __("Receiver's credit card is not specified."),
            __('Insufficient funds.')
        ])->view('pages.transaction_add', ['wallets_all' => $wallets]);

        $view->assertSee(__('My Wallets'));
        $view->assertSee(__('Transactions'));
        $view->assertSee(__('Log out'));

        $view->assertSee(__('From').':');
        $view->assertSee(__('To').':');
        $view->assertSee(__('Wallet'));
        $view->assertSee(__('Credit card'));
        $view->assertSee($wallets[0]->uniqid);
        $view->assertSee('0.00');
        $view->assertSee(__('Amount').':');
        $view->assertSee(__('Address').':');
        $view->assertSee(__('Card holder name').':');
        $view->assertSee(__('Card number').':');
        $view->assertSee(__('Expiry date').':');
        $view->assertSee(__('CVC').':');
        $view->assertSee(__('Add'));
        $view->assertSee(__('Cancel'));

        // Check errors.
        $view->assertSee(__('Whoops! Something went wrong.'));
        $view->assertSee(__("Sender's credit card is not specified."));
        $view->assertSee(__("Sender's credit card month not specified."));
        $view->assertSee(__("Sender's credit card year not specified."));
        $view->assertSee(__('Invalid credit card security code.'));
        $view->assertSee(__('Invalid transaction amount.'));
        $view->assertSee(__("Receiver's wallet is not specified."));
        $view->assertSee(__("Receiver's card holder name is not specified."));
        $view->assertSee(__("Receiver's credit card is not specified."));
        $view->assertSee(__('Insufficient funds.'));
    }

    /**
     * Try to mark a non-existing transaction as fraudulent.
     */
    public function testTransactionFraudulentNotExists()
    {
        $this->markTestIncomplete('This test is incomplete.');
    }

    /**
     * Mark transaction as fraudulent. This action is visible by both users.
     */
    public function testTransactionFraudulent()
    {
        // Need to check if the other user also sees this as fraudulent.
        $this->markTestIncomplete('This test is incomplete.');
    }

    /**
     * Successfully delete a transaction. This action is only for the current user
     */
    public function testTransactionDeleteSuccess()
    {
        $this->markTestIncomplete('This test is incomplete.');
    }

    /**
     * Try to delete a non-existing transaction.
     */
    public function testTransactionDeleteNotExists()
    {
        $this->markTestIncomplete('This test is incomplete.');
    }


    /**
     * Try to make a transaction from wallet with insufficient funds.
     */
    public function testTransactionAddInsufficient()
    {
        $this->markTestIncomplete('This test is incomplete.');
    }

    /**
     * Successfully add a new transaction from a credit card to a wallet.
     */
    public function testTransactionAddSuccessCardToWallet()
    {
        $this->createUser();
        $response = $this->createTransactionCardToWalletSelf();
        $response->assertSessionHasNoErrors();
        $response->assertRedirect('/transactions');
        $response->assertSessionHas('success', __('Transaction successfully created.'));
    }

    /**
     * Successfully add a new transaction from a wallet to a credit card.
     */
    public function testTransactionAddSuccessWalletToCard()
    {
        $this->createUser();
        // Add funds to wallet.
        $this->createTransactionCardToWalletSelf();

        $userid = auth()->user()->id;
        $wallet = Wallet::where('user_id', $userid)->first();

        $this->transaction_data = [
            'type_from' => \Config::get('transactions.type.wallet'),
            'type_to' => \Config::get('transactions.type.card'),
            // Send money form card from this user's wallet.
            'wallet_from' => $wallet->id,
            'card_to' => '2222-2222-2222-2222',
            'holder_to' => 'Jane Doe',
            'amount' => '100',
            '_token' => $this->user_data['_token']
        ];

        $response = $this->post('/transaction', $this->transaction_data);
        $response->assertSessionHasNoErrors();
        $response->assertRedirect('/transactions');
        $response->assertSessionHas('success', __('Transaction successfully created.'));
    }

    /**
     * Successfully add a new transaction from a user's one wallet to another wallet that belongs to same user.
     */
    public function testTransactionAddSuccessWalletToWalletSelf()
    {
        $this->markTestIncomplete('This test is incomplete.');
    }

    /**
     * Successfully add a new transaction from a user's wallet to another user's wallet.
     */
    public function testTransactionAddSuccessWalletToWalletOther()
    {
        $this->markTestIncomplete('This test is incomplete.');
    }

    /**
     * Try to add a transaction from a non-existing wallet.
     */
    public function testTransactionAddWalletNotExistsSelf()
    {
        $this->markTestIncomplete('This test is incomplete.');
    }

    /**
     * Try to add a transaction to a non-existing wallet.
     */
    public function testTransactionAddWalletNotExistsOther()
    {
        $this->markTestIncomplete('This test is incomplete.');
    }

    /**
     * Try to add a transaction from an unspecified source. For example, bank type is not implemented.
     */
    public function testTransactionAddInvalidSenderType()
    {
        $this->createUser();
        $this->transaction_data = [
            'type_from' => '',
            'type_to' => \Config::get('transactions.type.card'),
            'card_to' => '2222-2222-2222-2222',
            'holder_to' => 'Jane Doe',
            'amount' => '100',
            '_token' => $this->user_data['_token']
        ];
        $response = $this->post('/transaction', $this->transaction_data);
        $response->assertSessionHasErrors([
            'type_from' => __('Transaction type of sender is not specified.')
        ]);

        $this->transaction_data['type_from'] = '123';
        $response = $this->post('/transaction', $this->transaction_data);
        $response->assertSessionHasErrors([
            'type_from' => __('Invalid sender transaction type.')
        ]);
    }

    /**
     * Try to add a transaction from a credit card to a credit card. This operation is not allowed.
     */
    public function testTransactionAddCardToCardError()
    {
        $this->createUser();
        $this->transaction_data = [
            'type_from' => \Config::get('transactions.type.card'),
            'type_to' => \Config::get('transactions.type.card'),
            'card_to' => '2222-2222-2222-2222',
            'holder_to' => 'Jane Doe',
            'amount' => '100',
            '_token' => $this->user_data['_token']
        ];

        $response = $this->post('/transaction', $this->transaction_data);
        $response->assertSessionHasErrors([
            'type_to' => __('Invalid receiver transaction type.')
        ]);
    }

    /**
     * Try to add a transaction from a credit card by empty card credentials.
     */
    public function testTransactionAddCardEmptyCardCredentialsSelf()
    {
        $this->createUser();
        $userid = auth()->user()->id;
        $wallet = Wallet::where('user_id', $userid)->first();

        // No credentials given.
        $this->transaction_data = [
            'type_from' => \Config::get('transactions.type.card'),
            'type_to' => \Config::get('transactions.type.wallet'),
            'holder_from' => '',
            'card_from' => '',
            'date_m' => '',
            'date_y' => '',
            'cvc' => '',
            'amount' => '',
            'wallet_to' => '',
            '_token' => $this->user_data['_token']
        ];

        $response = $this->post('/transaction', $this->transaction_data);
        $response->assertSessionHasErrors([
            'amount' => __('Invalid transaction amount.'),
            'wallet_to' => __("Receiver's wallet is not specified."),
            'holder_from' => __("Sender's card holder name is not specified."),
            'card_from' => __("Sender's credit card is not specified."),
            'date_m' => __("Sender's credit card month not specified."),
            'date_y' => __("Sender's credit card year not specified."),
            'cvc' => __('Invalid credit card security code.')
        ]);
    }

    /**
     * Try to add a transaction from a credit card by giving invalid card credentials, like number, date in past etc.
     */
    public function testTransactionAddCardInvalidCardCredentialsSelf()
    {
        $this->markTestIncomplete('This test is incomplete.');
    }

    /**
     * Try to add a transaction to a credit card by giving invalid card number.
     */
    public function testTransactionAddCardInvalidCredentialsOther()
    {
        $this->markTestIncomplete('This test is incomplete.');
    }

    /**
     * Check if the totals of all wallets are correct.
     */
    public function testTransactionListAllWalletsTotals()
    {
        $this->markTestIncomplete('This test is incomplete.');
    }

    /**
     * Check if the the totals of one wallet are correct.
     */
    public function testTransactionListOneWalletTotals()
    {
        $this->markTestIncomplete('This test is incomplete.');
    }

    /**
     * Check if the incoming total of all wallets is correct.
     */
    public function testTransactionListAllWalletsIncoming()
    {
        $this->markTestIncomplete('This test is incomplete.');
    }

    /**
     * Check if the outgoing total of all wallets is correct.
     */
    public function testTransactionListAllWalletsOutgoing()
    {
        $this->markTestIncomplete('This test is incomplete.');
    }

    /**
     * Check if the incoming total of one wallet is correct.
     */
    public function testTransactionListOneWalletIncoming()
    {
        $this->markTestIncomplete('This test is incomplete.');
    }

    /**
     * Check if the outgoing total of one wallet is correct.
     */
    public function testTransactionListOneWalletOutgoing()
    {
        $this->markTestIncomplete('This test is incomplete.');
    }

    /**
     * Function used to create same user multiple times.
     *
     * @return Illuminate\Testing\TestResponse
     */
    private function createUser()
    {
        $this->get('/register');
        $this->user_data['_token'] = session('_token');

        return $this->post('register', $this->user_data);
    }

    private function createSecondWallet()
    {
        $userid = auth()->user()->id;

        // Add another wallet so that there is a possibility to operate between them.
        $data = [
            'name' => 'My second Virtual Wallet',
            '_token' => $this->user_data['_token']
        ];

        $this->post('/wallets/add', $data);

        return Wallet::where('user_id', $userid)->get();
    }

    private function createTransactionCardToWalletSelf()
    {
        $userid = auth()->user()->id;
        $wallet = Wallet::where('user_id', $userid)->first();

        $this->transaction_data += [
            'type_from' => \Config::get('transactions.type.card'),
            'type_to' => \Config::get('transactions.type.wallet'),
            // Send money form card to this user's wallet.
            'wallet_to' => $wallet->uniqid,
            '_token' => $this->user_data['_token']
        ];

        return $this->post('/transaction', $this->transaction_data);
    }
}
