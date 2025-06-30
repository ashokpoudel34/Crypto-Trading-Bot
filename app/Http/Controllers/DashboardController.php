<?php

namespace App\Http\Controllers;

use App\Models\Portfolio;
use App\Models\RealizedPnl;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Services\CoinGeckoService;

class DashboardController extends Controller
{
public function index(CoinGeckoService $coingecko)
{
    $transactions = Transaction::where('user_id', auth()->id())
        ->with('coin')
        ->latest()
        ->limit(10)
        ->get();
        
    $portfolio = Portfolio::where('user_id', auth()->id())
        ->with('coin')
        ->get();

    $coinIds = $portfolio->pluck('coin.coin_id')->unique()->values()->toArray();

    $prices = $coingecko->getPrices($coinIds); // Implement batch fetching in this method

    $portfolio = $portfolio->map(function($item) use ($prices) {
        $currentPrice = $prices[$item->coin->coin_id] ?? 0;
        $item->current_price = $currentPrice;
        $item->current_value = $item->amount * $currentPrice;
        $item->profit_loss = $item->current_value - ($item->amount * $item->average_buy_price);
        return $item;
    });

    $totalValue = $portfolio->sum('current_value');
    $totalUnRealizedPnl = $portfolio->sum('profit_loss');

    $realizedPnl = RealizedPnl::where('user_id', auth()->id())->first();

    $totalRealizedPnl = $realizedPnl?->total_realized_pnl ?? 0;

    return view('dashboard', compact('transactions', 'portfolio', 'totalValue', 'totalUnRealizedPnl', 'totalRealizedPnl'));
}

public function toggleAutoTrade(Request $request)
{
    $user = auth()->user();
    $user->auto_trade_enabled = $request->has('auto_trade_enabled');
    $user->save();

    return back();
}

public function sell($coinId)
{
    $userId = auth()->id();

    // Get the user's portfolio entry
    $holding = Portfolio::where('user_id', $userId)
        ->where('coin_id', $coinId)
        ->with('coin')
        ->first();

    if (!$holding || $holding->amount <= 0) {
        return redirect()->back()->with('status', 'No holdings to sell.');
    }

    // Fetch current price
    $coinIdApi = $holding->coin->coin_id;
    $coinGecko = app(\App\Services\CoinGeckoService::class);
    $prices = $coinGecko->getPrices([$coinIdApi]);
    $currentPrice = $prices[$coinIdApi] ?? null;

    if (!$currentPrice) {
        return redirect()->back()->with('status', 'Unable to fetch current price.');
    }

    // Calculate values
    $soldAmount = $holding->amount;
    $valueUsd = $soldAmount * $currentPrice;
    $averageBuyPrice = $holding->average_buy_price;
    $realizedPnl = ($currentPrice - $averageBuyPrice) * $soldAmount;

    // Create transaction
    Transaction::create([
        'user_id' => $userId,
        'coin_id' => $coinId,
        'amount' => $soldAmount,
        'price' => $currentPrice,
        'value_usd' => $valueUsd,
        'type' => 'sell',
    ]);

        $profitRow = RealizedPnl::firstOrNew(['user_id' => $userId]);
        $profitRow->total_realized_pnl += $realizedPnl;
        $profitRow->save();

    // Remove holding
    $holding->delete();

    return redirect()->back();
}



}
