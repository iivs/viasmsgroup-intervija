<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Tests\TestCase;

class UserTest extends TestCase
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
     * If user is not logged in, user is then redirected to login page.
     */
    public function testRedirectToLogin()
    {
        // Home page with wallet list.
        $response = $this->get('/');
        $response->assertRedirect('/login');

        // Transactions page.
        $response = $this->get('/transactions');
        $response->assertRedirect('/login');
    }

    /**
     * View default registration form and some possible errors.
     */
    public function testRegistrationForm()
    {
        // Test request.
        $response = $this->get('/register');
        $response->assertStatus(Response::HTTP_OK);

        // Test view with errors added.
        $view = $this->withViewErrors([
            'name' => __('Name is required.'),
            'email' => __('E-mail is required.'),
            'password' => __('Password is required.')
        ])->view('pages.register');

        $view->assertSee(__('Register'));
        $view->assertSee(__('Name').':');
        $view->assertSee(__('E-mail').':');
        $view->assertSee(__('Password').':');
        $view->assertSee(__('Confirm password').':');

        // Test some errors.
        $view->assertSee(__('Whoops! Something went wrong.'));
        $view->assertSee(__('Name is required.'));
        $view->assertSee(__('E-mail is required.'));
        $view->assertSee(__('Password is required.'));
    }

    /**
     * Navigate to registration page and try to post empty fields.
     */
    public function testRegistrationRequiredFields()
    {
        $this->get('/register');

        $this->user_data = [
            'name' => '',
            'email' => '',
            'password' => '',
            'password_confirmation' => '',
            '_token' => session('_token')
        ];

        $response = $this->post('register', $this->user_data);
        $response->assertSessionHasErrors([
            'name' => __('Name is required.'),
            'email' => __('E-mail is required.'),
            'password' => __('Password is required.')
        ]);
    }

    /**
     * Navigate to registration page and try to register with invalid e-mail address.
     */
    public function testRegistrationInvalidEmail()
    {
        $this->get('/register');

        $this->user_data['email'] = 'invalid-email-address';
        $this->user_data['_token'] = session('_token');

        $response = $this->post('register', $this->user_data);
        $response->assertSessionHasErrors([
            'email' => __('The email must be a valid email address.')
        ]);

        // Test the view with different errors.
        $view = $this->withViewErrors([
            'email' => __('The email must be a valid email address.')
        ])->view('pages.register');

        $view->assertSee(__('Whoops! Something went wrong.'));
        $view->assertSee(__('The email must be a valid email address.'));
    }

    /**
     * Navigate to registration page and try to register with empty password or different confirmation password.
     */
    public function testRegistrationPasswordMismatch()
    {
        $this->get('/register');
        $this->user_data['password_confirmation'] = '';
        $this->user_data['_token'] = session('_token');

        $response = $this->post('register', $this->user_data);
        $response->assertSessionHasErrors([
            'password' => __('Passwords do not match.')
        ]);

        // Test request with different password.
        $this->user_data['password'] = 'password1';
        $this->user_data['password_confirmation'] = 'password2';

        $response = $this->post('register', $this->user_data);
        $response->assertSessionHasErrors([
            'password' => __('Passwords do not match.')
        ]);

        // Test the view.
        $view = $this->withViewErrors([
            'password' => __('Passwords do not match.')
        ])->view('pages.register');

        $view->assertSee(__('Whoops! Something went wrong.'));
        $view->assertSee(__('Passwords do not match.'));
    }

    /**
     * Navigate to registration page and register. Observe there are no errors, and user is redirected to wallet list
     * with a success message.
     */
    public function testRegistrationUserSuccess()
    {
        $this->get('/register');
        $this->user_data['_token'] = session('_token');

        // User is logged in with a success message and redirect to home page.
        $response = $this->post('register', $this->user_data);
        $response->assertSessionHasNoErrors();
        $response->assertRedirect('/');
        $response->assertSessionHas('success',
            __('Thank you for registering! We have created your first virtual wallet.')
        );
    }

    /**
     * Navigate to registration page and register. Then log out. Observe there are no errors and there is a success
     * message. User is also automatically redirected to login page.
     */
    public function testLogout()
    {
        $this->get('/register');
        $this->user_data['_token'] = session('_token');
        $this->post('register', $this->user_data);

        // Log out user and see a goodbye success message.
        $response = $this->post('/logout', ['_token' => $this->user_data['_token']]);
        $response->assertSessionHasNoErrors();
        $response->assertRedirect('/login');
        $response->assertSessionHas('success',
            __('Thank you for using Virtual Wallet! We hope to see you again.')
        );
    }

    /**
     * Navigate to registration page and try register with same credentials and observe the error.
     */
    public function testRegistrationUserExists()
    {
        $this->get('/register');
        $this->user_data['_token'] = session('_token');
        $this->post('register', $this->user_data);
        $this->post('/logout', ['_token' => $this->user_data['_token']]);

        // Register a user with same e-mail and get and error.
        $response = $this->post('register', $this->user_data);
        $response->assertSessionHasErrors([
            'email' => __('User with this e-mail already exists.')
        ]);
    }

    /**
     * Navigate to registration page and register. Log out user and then log back in using the e-mail and password.
     * Observe that user is redirected to wallet list and there are no errors.
     */
    public function testLoginSuccess()
    {
        $this->get('/register');
        $this->user_data['_token'] = session('_token');
        $this->post('register', $this->user_data);
        $this->post('/logout', ['_token' => $this->user_data['_token']]);
        
        // Log back in.
        $this->user_data = [
            'email' => 'john@doe.com',
            'password' => 'password',
            '_token' => session('_token')
        ];

        $response = $this->post('login', $this->user_data);
        $response->assertSessionHasNoErrors();
        $response->assertRedirect('/');
    }

    /**
     * Navigate to login page. Log in using invalid user e-mail and password.
     */
    public function testLoginInvalidUser()
    {
        $this->get('/login');

        // Log in with non-existing user. DB can be empty as well.
        $this->user_data = [
            'email' => 'john@doe.com',
            'password' => 'password',
            '_token' => session('_token')
        ];

        $response = $this->post('login', $this->user_data);

        // Test the view.
        $view = $this->withViewErrors([
            __('Invalid e-mail or password.')
        ])->view('pages.login');

        $view->assertSee(__('Whoops! Something went wrong.'));
        $view->assertSee(__('Invalid e-mail or password.'));

        /*
         * There are errors in session('errors') and they should be there, but they cannot be asserted using
         * assertSessionHasErrors. The error message is returned "Invalid e-mail or password."
         */
        $this->markTestIncomplete('This test is incomplete.');
    }
}
