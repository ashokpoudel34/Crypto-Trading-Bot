<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WalletController extends Controller
{
        public function show()
    {
        $user = auth()->user();
        return view('wallet.show', compact('user'));
    }

    public function reload(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
        ]);

        $user = auth()->user();
        $user->balance += $request->amount;
        $user->save();

        return back()->with('status', 'Balance reloaded successfully!');
    }
}
