<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class UsersController extends Controller
{
    /**
     * If user is not logged in show login page. Otherwise redirect to users' wallets.
     *
     * @return Illuminate\View\View|Illuminate\Http\RedirectResponse
     */
    public function index()
    {
        if (Auth::user()) {
            return redirect('/');
        }

        return view('pages.login');
    }

    /**
     * Find and authenticate user by given e-mail and then redirect to users' wallets.
     *
     * @param Illuminate\Http\Request $request
     *
     * @return Illuminate\Http\RedirectResponse
     */
    public function login(Request $request)
    {
        $user = User::where('email', $request->input('email'))->first();

        if ($user === null || !Hash::check($request->input('password'), $user->password)) {
            return back()
                ->withErrors(__('Invalid e-mail or password.'))
                ->withInput($request->only('email'));
        }

        Auth::login($user);

        return redirect('/');
    }

    /**
     * If user is logged in, redirect back to wallets. Otherwise show registation page.
     * 
     * @return Illuminate\View\View|Illuminate\Http\RedirectResponse
     */
    public function register()
    {
        if (Auth::user()) {
            return redirect('/');
        }

        return view('pages.register');
    }

    /**
     * Handle the new user creation. If user is created, create his first wallet, add a bonus for registering,
     * log in the user and redirect to users' wallets.
     *
     * @param Illuminate\Http\Request $request
     *
     * @return Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Create custom error messages for user registration.
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|confirmed'
        ], [
            'name.required' => __('Name is required.'),
            'email.required' => __('E-mail is required.'),
            'email.unique' => __('User with this e-mail already exists.'),
            'password.required' => __('Password is required.'),
            'password.confirmed' => __('Passwords do not match.')
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();

            return back()
                ->withErrors($errors)
                ->withInput($request->only('name', 'email'));
        }

        $user = User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password'))
        ]);

        // Create a wallet for new user and add bonus money for registering. Bonus money is added automatically from DB.
        if ($user) {
            $user->wallets()->create([
                'name' => __('My first Virtual Wallet'),
                'uniqid' => strtoupper(uniqid())
            ]);
        }

        // Log user in, redirect to wallet list and send a warm welcome message.
        Auth::login($user);

        return redirect()
            ->route('wallet.list')
            ->withSuccess(__('Thank you for registering! We have created your first virtuall wallet and added a bonus to your account.'));
    }

    /**
     * Handle the request for log out and send a message if user is logged out.
     *
     * @return Illuminate\Http\RedirectResponse
     */
    public function logout()
    {
        Auth::logout();

        return redirect()
            ->route('login')
            ->withSuccess(__('Thank you for using Virtual Wallet! We hope to see you again.'));
    }
}
