<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\WalletsController;
use App\Http\Controllers\TransactionsController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Shows the user login form.
Route::get('/login', [UsersController::class, 'index'])->name('login');

// Shows the user registration form.
Route::get('/register', [UsersController::class, 'register'])->name('register');

// Performs the login action.
Route::post('/login', [UsersController::class, 'login'])->name('user.login');

// Performs the creation of new user.
Route::post('/register', [UsersController::class, 'store'])->name('user.register');

// Protected routes are only accessible if user is logged in.
Route::group(['middleware' => ['auth']], function() {
    // Performs the log out of the current user.
    Route::post('/logout', [UsersController::class, 'logout'])->name('logout');

    // Displays the wallet list.
    Route::get('/', [WalletsController::class, 'index'])->name('wallet.list');

    // Shows the wallet edit form.
    Route::get('/wallet/{id}', [WalletsController::class, 'edit'])->name('wallet.edit');

    // Shows a single wallet with transactions.
    Route::get('/wallet/{id}/transactions', [WalletsController::class, 'show'])->name('wallet.show');

    // Shows the wallet create form.
    Route::get('/wallets/add', [WalletsController::class, 'add'])->name('wallet.add');

    // Performs the creation of a new wallet.
    Route::post('/wallets/add', [WalletsController::class, 'store'])->name('wallet.store');

    // Performs update on a wallet.
    Route::put('/wallet/{id}', [WalletsController::class, 'update'])->name('wallet.update');

    // Performs the deletion of wallet.
    Route::delete('/wallet/{id}', [WalletsController::class, 'destroy'])->name('wallet.delete');

    // Show list of all transactions from all wallets.
    Route::get('/transactions/{id?}', [TransactionsController::class, 'index'])->name('transactions.all');

    // Show list of transactions from a single wallet.
    Route::get('/transactions/{id}/{param?}', [TransactionsController::class, 'index'])->name('transactions.one');

    // Show transaction create form.
    Route::get('/transaction/{id?}', [TransactionsController::class, 'add'])->name('transaction.add');

    // Perform the creation of transaction.
    Route::post('/transaction', [TransactionsController::class, 'store'])->name('transaction.store');

    // Performs the deletion of transaction.
    Route::delete('/transaction/{id}', [TransactionsController::class, 'destroy'])->name('transaction.delete');

    // Updates transaction status to fraudulent or back, if user changed his mind.
    Route::put('/transaction/{id}', [TransactionsController::class, 'update'])->name('transaction.update');
});
