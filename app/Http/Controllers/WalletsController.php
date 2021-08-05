<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Wallet;

class WalletsController extends Controller
{
    public function index() {
        $userid = Auth::user()->id;
        $wallets = Wallet::where('user_id', $userid)->paginate(20);

        return view('pages.wallets', compact('wallets'));
    }

    public function edit($id) {
        return view('pages.wallet_edit');
    }
}
