<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Tests\TestCase;

class UserTest extends TestCase
{
    public function testHomeRedirectToLogin()
    {
        $response = $this->get('/');
        $response->assertRedirect('/login');
    }

    public function testRegistrationForm()
    {
        // Test request.
        $response = $this->get('/register');
        $response->assertStatus(Response::HTTP_OK);

        // Test view.
        $view = $this->view('pages.register');
        $view->assertSee(__('Register'));
        $view->assertSee(__('Name').':');
        $view->assertSee(__('E-mail').':');
        $view->assertSee(__('Password').':');
        $view->assertSee(__('Confirm password').':');
    }

    public function testRegistrationRequiredFields()
    {
        // Navigate to register page and obtain a token.
        $response = $this->get('/register');
        $response->assertStatus(Response::HTTP_OK);

        $token = session('_token');

        // Test request.
        $data = [
            'name' => '',
            'email' => '',
            'password' => '',
            'password_confirmation' => '',
            '_token' => $token
        ];

        $response = $this->post('register', $data);
        $response->assertSessionHasErrors([
            'name' => __('Name is required.'),
            'email' => __('E-mail is required.'),
            'password' => __('Password is required.')
        ]);

        // Test the view.
        $view = $this->withViewErrors([
            'name' => __('Name is required.'),
            'email' => __('E-mail is required.'),
            'password' => __('Password is required.')
        ])->view('pages.register');

        $view->assertSee(__('Whoops! Something went wrong.'));
        $view->assertSee(__('Name is required.'));
        $view->assertSee(__('E-mail is required.'));
        $view->assertSee(__('Password is required.'));
    }

    public function testRegistrationInvalidEmail()
    {
        // Navigate to register page and obtain a token.
        $response = $this->get('/register');
        $response->assertStatus(Response::HTTP_OK);
        $token = session('_token');

        // Test request.
        $data = [
            'name' => 'John Doe',
            'email' => 'invalid-email-address',
            'password' => 'password',
            'password_confirmation' => 'password',
            '_token' => $token
        ];

        $response = $this->post('register', $data);
        $response->assertSessionHasErrors([
            'email' => __('The email must be a valid email address.')
        ]);

        // Test the view.
        $view = $this->withViewErrors([
            'email' => __('The email must be a valid email address.')
        ])->view('pages.register');

        $view->assertSee(__('Whoops! Something went wrong.'));
        $view->assertSee(__('The email must be a valid email address.'));
    }

    public function testRegistrationPasswordMismatch()
    {
        // Navigate to register page and obtain a token.
        $response = $this->get('/register');
        $response->assertStatus(Response::HTTP_OK);
        $token = session('_token');

        // Test request with empty password confirmation.
        $data = [
            'name' => 'John Doe',
            'email' => 'john@doe.com',
            'password' => 'password',
            'password_confirmation' => '',
            '_token' => $token
        ];

        $response = $this->post('register', $data);
        $response->assertSessionHasErrors([
            'password' => __('Passwords do not match.')
        ]);

        // Test request with different password.
        $data = [
            'name' => 'John Doe',
            'email' => 'john@doe.com',
            'password' => 'password1',
            'password_confirmation' => 'password2',
            '_token' => $token
        ];

        $response = $this->post('register', $data);
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

    public function testRegistrationUserSuccess()
    {
        // Navigate to register page and obtain a token.
        $response = $this->get('/register');
        $response->assertStatus(Response::HTTP_OK);
        $token = session('_token');

        // Register a user.
        $data = [
            'name' => 'John Doe',
            'email' => 'john@doe.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            '_token' => $token
        ];

        // User is logged in with a success message and redirect to home page.
        $response = $this->post('register', $data);
        $response->assertSessionHasNoErrors();
        $response->assertRedirect('/');
        $response->assertSessionHas('success',
            __('Thank you for registering! We have created your first virtual wallet.')
        );
    }

    public function testLogout()
    {
        // Navigate to register page and obtain a token.
        $response = $this->get('/register');
        $response->assertStatus(Response::HTTP_OK);
        $token = session('_token');

        // Register a user.
        $data = [
            'name' => 'John Doe',
            'email' => 'john@doe.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            '_token' => $token
        ];

        // User is logged in with a success message and redirect to home page.
        $response = $this->post('register', $data);
        $response->assertSessionHasNoErrors();
        $response->assertRedirect('/');
        $response->assertSessionHas('success',
            __('Thank you for registering! We have created your first virtual wallet.')
        );

        // Log out user and see a goodbye success message.
        $response = $this->post('/logout', ['_token' => $token]);
        $response->assertSessionHasNoErrors();
        $response->assertRedirect('/login');
        $response->assertSessionHas('success',
            __('Thank you for using Virtual Wallet! We hope to see you again.')
        );
    }

    public function testRegistrationUserExists()
    {
        // Navigate to register page and obtain a token.
        $response = $this->get('/register');
        $response->assertStatus(Response::HTTP_OK);
        $token = session('_token');

        // Register a user.
        $data = [
            'name' => 'John Doe',
            'email' => 'john@doe.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            '_token' => $token
        ];

        // User is logged in with a success message and redirect to home page.
        $response = $this->post('register', $data);
        $response->assertSessionHasNoErrors();
        $response->assertRedirect('/');
        $response->assertSessionHas('success',
            __('Thank you for registering! We have created your first virtual wallet.')
        );

        // Log out user and see a goodbye success message.
        $response = $this->post('/logout', ['_token' => $token]);
        $response->assertSessionHasNoErrors();
        $response->assertRedirect('/login');
        $response->assertSessionHas('success',
            __('Thank you for using Virtual Wallet! We hope to see you again.')
        );

        // Register a user with same e-mail and get and error.
        $response = $this->post('register', $data);
        $response->assertSessionHasErrors([
            'email' => __('User with this e-mail already exists.')
        ]);
    }

    public function testLoginSuccess()
    {
        // Navigate to register page and obtain a token.
        $response = $this->get('/register');
        $response->assertStatus(Response::HTTP_OK);
        $token = session('_token');

        // Register a user.
        $data = [
            'name' => 'John Doe',
            'email' => 'john@doe.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            '_token' => $token
        ];

        // User is logged in with a success message and redirect to home page.
        $response = $this->post('register', $data);
        $response->assertSessionHasNoErrors();
        $response->assertRedirect('/');
        $response->assertSessionHas('success',
            __('Thank you for registering! We have created your first virtual wallet.')
        );

        // Log out user and see a goodbye success message.
        $response = $this->post('/logout', ['_token' => $token]);
        $response->assertSessionHasNoErrors();
        $response->assertRedirect('/login');
        $response->assertSessionHas('success',
            __('Thank you for using Virtual Wallet! We hope to see you again.')
        );

        // Log back in.
        $data = [
            'email' => 'john@doe.com',
            'password' => 'password',
            '_token' => $token
        ];

        $response = $this->post('login', $data);
        $response->assertSessionHasNoErrors();
        $response->assertRedirect('/');
    }

    public function testLoginInvalidUser()
    {
        // Navigate to register page and obtain a token.
        $response = $this->get('/login');
        $response->assertStatus(Response::HTTP_OK);
        $token = session('_token');

        // Log in with non-existing user. DB can be empty as well.
        $data = [
            'email' => 'john2@doe2.com',
            'password' => 'password2',
            '_token' => $token
        ];

        $response = $this->post('login', $data);

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

