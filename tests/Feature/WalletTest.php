<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Tests\TestCase;
use App\Models\Wallet;

class WalletTest extends TestCase
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
     * Create a user and navigate to wallet list page. Then check if the view has the necessary data.
     */
    public function testWalletListAfterLogin()
    {
        $this->createUser();
        $this->get('/');

        // Get user ID and wallet that was made for the user after registration.
        $userid = auth()->user()->id;
        $wallets = Wallet::where('user_id', $userid)->paginate(20);

        // Test the wallet list view.
        $view = $this->view('pages.wallets', compact('wallets'));
        $view->assertSee(__('My Wallets'));
        $view->assertSee(__('Transactions'));
        $view->assertSee(__('Log out'));
        $view->assertSee('+ '.__('Add wallet'));
        $view->assertSee(__('ID'));
        $view->assertSee(__('Name'));
        $view->assertSee(__('Wallet address'));
        $view->assertSee(__('Balance').' €');
        $view->assertSee(__('My first Virtual Wallet'));
        $view->assertSee('0.00');
        $view->assertSee(__('View'));
        $view->assertSee(__('Edit'));
        $view->assertSee(__('Delete'));
    }

    /**
     * Create a user and try to access non-existing wallet. Either ID does not exist or user has no access to that
     * wallet.
     */
    public function testWalletNotExists()
    {
        $this->createUser();

        // Try to access a non-existing wallet. Since DB is clean, this ID will not exist.
        $response = $this->get('/wallet/9999/transactions');

        /*
         * There are errors in session('errors') and they should be there, but they cannot be asserted using
         * assertSessionHasErrors. The error message is returned "The wallet does not exist."
         */
        $this->markTestIncomplete('This test is incomplete.');
    }

    /**
     * Create a user and navigate to single wallet view. Then check if the view has the necessary data.
     */
    public function testWalletView()
    {
        $this->createUser();

        // Get ID of the first possible wallet and try to access the view form.
        $userid = auth()->user()->id;
        $wallet = Wallet::where('user_id', $userid)->first();

        $response = $this->get('/wallet/'.$wallet->id.'/transactions');
        $response->assertStatus(Response::HTTP_OK);
        $response->assertSessionHasNoErrors();

        $view = $this->view('pages.wallet', compact('wallet'));
        $view->assertSee(__('My Wallets'));
        $view->assertSee(__('Transactions'));
        $view->assertSee(__('Log out'));
        $view->assertSee(__('Wallet').':');
        $view->assertSee(__('Address').':');
        $view->assertSee($wallet->uniqid);
        $view->assertSee(__('Balance').':');
        $view->assertSee(__('Last edited').':');
        $view->assertSee(__('My first Virtual Wallet'));
        $view->assertSee('0.00 €');
        $view->assertSee(__('Edit'));
        $view->assertSee(__('Delete'));
        $view->assertSee(__('Back'));
    }

    /**
     * Create a user and navigate to wallet edit view. Then check if the view has the necessary data.
     */
    public function testWalletEditForm()
    {
        $this->createUser();

        // Get ID of the first possible wallet and try to access the view form.
        $userid = auth()->user()->id;
        $wallet = Wallet::where('user_id', $userid)->first();

        $response = $this->get('/wallet/'.$wallet->id);
        $response->assertStatus(Response::HTTP_OK);
        $response->assertSessionHasNoErrors();

        // Navigate to wallet edit form.
        $view = $this->view('pages.wallet_edit', [
            'wallet' => $wallet,
            'name' => $wallet->name,
            'id' => $wallet->id
        ]);
        $view->assertSee(__('My Wallets'));
        $view->assertSee(__('Transactions'));
        $view->assertSee(__('Log out'));
        $view->assertSee(__('Name').':');
        $view->assertSee(__('My first Virtual Wallet'));
        $view->assertSee(__('Save'));
        $view->assertSee(__('Delete'));
        $view->assertSee(__('Cancel'));
    }

    /**
     * Create a user and navigate to new wallet creation form view. Then check if the view has the necessary data.
     */
    public function testWalletAddForm()
    {
        $this->createUser();

        // Navigate to wallet creation page.
        $response = $this->get('/wallets/add');
        $response->assertStatus(Response::HTTP_OK);

        $view = $this->view('pages.wallet_add');
        $view->assertSee(__('My Wallets'));
        $view->assertSee(__('Transactions'));
        $view->assertSee(__('Log out'));
        $view->assertSee(__('Name').':');
        $view->assertSee(__('Save'));
        $view->assertSee(__('Cancel'));
    }

    /**
     * Create a user and try to add a new wallet with an empty name. Observe response and that view has errors.
     */
    public function testWalletAddNameEmpty()
    {
        $this->createUser();

        // Try to add a wallet without name.
        $data = [
            'name' => '',
            '_token' => $this->user_data['_token']
        ];
        $response = $this->post('/wallets/add', $data);

        $response->assertSessionHasErrors([
            'name' => __('The name field is required.')
        ]);

        $view = $this->withViewErrors([
            __('This wallet name is taken.'),
            __('The name field is required.')
        ])->view('pages.wallet_add');

        $view->assertSee(__('Whoops! Something went wrong.'));
        $view->assertSee(__('This wallet name is taken.'));
        $view->assertSee(__('The name field is required.'));
    }

    /**
     * Create a user and try to add a new wallet with an existing name. Wallet names are case insensitive.
     */
    public function testWalletAddNameExists()
    {
        $this->createUser();

        // Try to add a wallet with existing name.
        $data = [
            // Notice the case.
            'name' => 'my first virtual wallet',
            '_token' => $this->user_data['_token']
        ];
        $response = $this->post('/wallets/add', $data);

        /*
         * There are errors in session('errors') and they should be there, but they cannot be asserted using
         * assertSessionHasErrors. The error message is returned "This wallet name is taken."
         */
        $this->markTestIncomplete('This test is incomplete.');
    }

    /**
     * Create a user and add a new wallet. Observe that there are no errors and there is a success message.
     */
    public function testWalletAddSuccess()
    {
        $this->createUser();

        // Try to add a wallet with a new name.
        $data = [
            'name' => 'My second Virtual Wallet',
            '_token' => $this->user_data['_token']
        ];
        $response = $this->post('/wallets/add', $data);
        $response->assertSessionHasNoErrors();
        $response->assertRedirect('/');
        $response->assertSessionHas('success', __('Wallet successfully created.'));
    }

    /**
     * Create a user and try to update wallet with an empty name. Observe that there are errors in response and view.
     */
    public function testWalletUpdateNameEmpty()
    {
        $this->createUser();

        // Get ID of the first possible wallet and try to access the view form.
        $userid = auth()->user()->id;
        $wallet = Wallet::where('user_id', $userid)->first();

        // Navigate to edit mode.
        $response = $this->get('/wallet/'.$wallet->id);
        $response->assertStatus(Response::HTTP_OK);
        $response->assertSessionHasNoErrors();

        // Try to update a wallet without name.
        $data = [
            'name' => '',
            '_token' => $this->user_data['_token']
        ];
        $response = $this->put('/wallet/'.$wallet->id, $data);

        $response->assertSessionHasErrors([
            'name' => __('The name field is required.')
        ]);

        $view = $this->withViewErrors([
            __('This wallet name is taken.'),
            __('The name field is required.')
        ])->view('pages.wallet_add');

        $view->assertSee(__('Whoops! Something went wrong.'));
        $view->assertSee(__('This wallet name is taken.'));
        $view->assertSee(__('The name field is required.'));
    }

    /**
     * Create a user and try to update non-existing wallet.
     */
    public function testWalletUpdateWalletNotExists()
    {
        $this->createUser();

        $data = [
            'name' => 'Some other wallet',
            '_token' => $this->user_data['_token']
        ];
        $response = $this->put('/wallet/999999', $data);

        /*
         * There are errors in session('errors') and they should be there, but they cannot be asserted using
         * assertSessionHasErrors. The error message is returned "The wallet does not exist."
         */
        $this->markTestIncomplete('This test is incomplete.');
    }

    /**
     * Create a user, add a another wallet. Update the first wallet by giving the name of the second wallet.
     */
    public function testWalletUpdateNameExists()
    {
        $this->createUser();

        // Add another wallet.
        $data = [
            'name' => 'My second Virtual Wallet',
            '_token' => $this->user_data['_token']
        ];
        $response = $this->post('/wallets/add', $data);
        $response->assertSessionHasNoErrors();
        $response->assertRedirect('/');
        $response->assertSessionHas('success', __('Wallet successfully created.'));

        // Get the ID of the first wallet and go to edit mode.
        $userid = auth()->user()->id;
        $wallet = Wallet::where('user_id', $userid)->first();

        // Try to update a wallet to existing name creted a moment ago.
        $data = [
            'name' => 'My second Virtual Wallet',
            '_token' => $this->user_data['_token']
        ];
        $response = $this->put('/wallet/'.$wallet->id, $data);

        /*
         * There are errors in session('errors') and they should be there, but they cannot be asserted using
         * assertSessionHasErrors. The error message is returned "This wallet name is taken."
         */
        $this->markTestIncomplete('This test is incomplete.');
    }

    /**
     * Create a user and successfully update the wallet name.
     */
    public function testWalletUpdateNameSuccess()
    {
        $this->createUser();

        // Get the ID of the first wallet and go to edit mode.
        $userid = auth()->user()->id;
        $wallet = Wallet::where('user_id', $userid)->first();

        // Try to update a wallet to existing name creted a moment ago.
        $data = [
            'name' => 'Wallet name updated',
            '_token' => $this->user_data['_token']
        ];
        $response = $this->put('/wallet/'.$wallet->id, $data);
        $response->assertSessionHasNoErrors();
        $response->assertRedirect('/');
        $response->assertSessionHas('success', __('Wallet successfully updated.'));
    }

    /**
     * Create a user and try to delete a non-existing wallet.
     */
    public function testWalletDeleteNotExists()
    {
        $this->createUser();

        $response = $this->delete('/wallet/999999', ['_token' => $this->user_data['_token']]);

        /*
         * There are errors in session('errors') and they should be there, but they cannot be asserted using
         * assertSessionHasErrors. The error message is returned "The wallet does not exist."
         */
        $this->markTestIncomplete('This test is incomplete.');
    }

    /**
     * Create a user and successfully delete a wallet. Observe that there are no errors, user is redirected back and
     * there is a success message.
     */
    public function testWalletDeleteSuccess()
    {
        $this->createUser();

        // Get the ID of the first wallet and go to edit mode.
        $userid = auth()->user()->id;
        $wallet = Wallet::where('user_id', $userid)->first();

        // Try to delete the existing wallet.
        $response = $this->delete('/wallet/'.$wallet->id, ['_token' => $this->user_data['_token']]);
        $response->assertSessionHasNoErrors();
        $response->assertRedirect('/');
        $response->assertSessionHas('success', __('Wallet successfully deleted.'));
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
}
