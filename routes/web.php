<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\WalletsController;

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

//Route::get('/')->middleware('auth');

// Public routes.
Route::get('/login', [UsersController::class, 'index'])->name('login');
Route::post('/login', [UsersController::class, 'login'])->name('user.login');
Route::get('/register', [UsersController::class, 'register'])->name('register');
Route::post('/register', [UsersController::class, 'store'])->name('user.register');

// Protected routes.
Route::group(['middleware' => ['auth']], function() {
    Route::post('/logout', [UsersController::class, 'logout'])->name('logout');
    Route::get('/', [WalletsController::class, 'index'])->name('wallet.list');
});
