<?php

namespace App\Http\Controllers;

use App\Models\Portfolio;
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
    $totalProfitLoss = $portfolio->sum('profit_loss');

    return view('dashboard', compact('transactions', 'portfolio', 'totalValue', 'totalProfitLoss'));
}

}
