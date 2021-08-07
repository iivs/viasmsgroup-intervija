<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Wallet;
use App\Models\Transaction;

class WalletsController extends Controller
{
    /**
     * Show user wallet list.
     *
     * @return Illuminate\View\View
     */
    public function index()
    {
        $userid = Auth::user()->id;
        $wallets = Wallet::where('user_id', $userid)->paginate(20);

        return view('pages.wallets', compact('wallets'));
    }

    /**
     * If the given wallet ID is valid, show the edit form. Otherwise, redirect back list view with error.
     *
     * @param  int $id  Wallet ID.
     *
     * @return Illuminate\View\View|Illuminate\Http\RedirectResponse
     */
    public function edit($id)
    {
        $userid = Auth::user()->id;
        $wallet = Wallet::where(['id' => $id, 'user_id' => $userid])->first();

        if ($wallet === null) {
            // It's possible that there is no way to redirect back(), so redirect to list instead and show error.
            return redirect()
                ->route('wallet.list')
                ->withErrors(__('The wallet does not exist.'));
        }

        return view('pages.wallet_edit', ['name' => $wallet->name, 'id' => $id]);
    }

    /**
     * Perform the update on wallet. Change name.
     * 
     * @param  Illuminate\Http\Request $request
     * @param  int $id  Wallet ID.
     *
     * @return Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $userid = Auth::user()->id;
        $wallet = Wallet::where(['id' => $id, 'user_id' => $userid])->first();

        if ($wallet === null) {
            return back()->withErrors(__('The wallet does not exist.'));
        }

        $request->validate([
            'name' => 'required|string'
        ]);

        $name = strtoupper($request->input('name'));

        /*
         * Check if the given name is for the same wallet. If it is, skip updating and return. Otherwise, check
         * if the wallet name alredy exists for this user. Wallet names are case insensitive.
         */
        if (strtoupper($wallet->name) !== $name) {
            $userid = Auth::user()->id;
            $wallet_exists = Wallet::whereRaw("UPPER(name) = '".$name."'")
                ->where('user_id', $userid)
                ->first();

            if ($wallet_exists) {
                return back()
                    ->withErrors(__('This wallet name is taken.'))
                    ->withInput($request->only('name'));
            }
        }

        $wallet->update($request->only('name'));

        return redirect()
                ->route('wallet.list')
                ->withSuccess(__('Wallet successfully updated.'));
    }

    /**
     * Soft-remove the wallet.
     *
     * @param  int  $id  Wallet ID.
     *
     * @return Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $userid = Auth::user()->id;
        $wallet = Wallet::where(['id' => $id, 'user_id' => $userid])->first();

        if ($wallet === null) {
            return redirect()
                ->route('wallet.list')
                ->withErrors(__('The wallet does not exist.'));
        }

        if (Wallet::destroy($id)) {
            // Find incoming and outgoing transactions of this wallet.
            $transactions = Transaction::where(
                function ($query) use ($wallet) {
                    $query
                        ->where('from', $wallet->uniqid)
                        ->orWhere('to', $wallet->uniqid);
                }
            )
            ->where(
                function ($query) use ($userid) {
                    $query->where('user_id', $userid);
                }
            )
            ->get();

            if ($transactions !== null) {
                Transaction::destroy($transactions);
            }

            return redirect()
                ->route('wallet.list')
                ->withSuccess(__('Wallet successfully deleted.'));
        }
    }

    /**
     * Display contents of wallet. Name, address, when it was created, balance and incoming/outgoing transactions.
     *
     * @param  int  $id  Wallet ID.
     *
     * @return Illuminate\View\View
     */
    public function show($id)
    {
        $userid = Auth::user()->id;
        $wallet = Wallet::where(['id' => $id, 'user_id' => $userid])->first();

        if ($wallet === null) {
            return back()->withErrors(__('The wallet does not exist.'));
        }

        return view('pages.wallet', compact('wallet'));
    }

    /**
     * Display the wallet create form.
     *
     * @return Illuminate\View\View
     */
    public function add()
    {
        $userid = Auth::user()->id;

        return view('pages.wallet_add');
    }

    /**
     * Perform creation of a new wallet.
     * 
     * @param  Illuminate\Http\Request $request
     *
     * @return Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string'
        ]);

        $userid = Auth::user()->id;
        $name = $request->input('name');

        // Check if the given name exists for this user. Wallet names are case insensitive.
        $wallet_exists = Wallet::whereRaw("UPPER(name) = '".strtoupper($name)."'")
            ->where('user_id', $userid)
            ->first();

        if ($wallet_exists) {
            return back()
                ->withErrors(__('This wallet name is taken.'))
                ->withInput($request->only('name'));
        }

        $wallet = Wallet::create([
            'user_id' => $userid,
            'name' => $name,
            'uniqid' => strtoupper(uniqid())
        ]);

        if ($wallet) {
            return redirect()
                ->route('wallet.list')
                ->withSuccess(__('Wallet successfully created.'));
        }
    }
}
