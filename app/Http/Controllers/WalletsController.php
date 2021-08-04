<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WalletsController extends Controller
{
    public function index() {
        return view('pages.wallets');
    }
}
