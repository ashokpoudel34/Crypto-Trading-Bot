<?php

namespace App\Http\Controllers;

use App\Models\Portfolio;
use App\Models\RealizedPnl;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Services\CoinGeckoService;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function index(CoinGeckoService $coingecko)
    {
        $userId = auth()->id();

        $transactions = Transaction::where('user_id', $userId)
            ->with('coin')
            ->latest()
            ->limit(10)
            ->get();

        $portfolio = Portfolio::where('user_id', $userId)
            ->with('coin')
            ->get();

        $coinIds = $portfolio->pluck('coin.coin_id')->unique()->values()->toArray();

        // Cache prices for 60 seconds to prevent hitting API rate limits
        $prices = Cache::remember("coin_prices_user_$userId", 60, function () use ($coingecko, $coinIds) {
            return $coingecko->getPrices($coinIds);
        });

        $portfolio = $portfolio->map(function ($item) use ($prices) {
            $currentPrice = $prices[$item->coin->coin_id] ?? 0;
            $item->current_price = $currentPrice;
            $item->current_value = $item->amount * $currentPrice;
            $item->profit_loss = $item->current_value - ($item->amount * $item->average_buy_price);
            return $item;
        });

        $totalValue = $portfolio->sum('current_value');
        $totalUnRealizedPnl = $portfolio->sum('profit_loss');

        $realizedPnl = RealizedPnl::where('user_id', $userId)->first();
        $totalRealizedPnl = $realizedPnl?->total_realized_pnl ?? 0;

        return view('dashboard', compact(
            'transactions',
            'portfolio',
            'totalValue',
            'totalUnRealizedPnl',
            'totalRealizedPnl'
        ));
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
        $user = auth()->user();
        $userId = $user->id;

        // Get the user's portfolio entry
        $holding = Portfolio::where('user_id', $userId)
            ->where('coin_id', $coinId)
            ->with('coin')
            ->first();

        if (!$holding || $holding->amount <= 0) {
            return redirect()->back()->with('status', 'No holdings to sell.');
        }

        $coinIdApi = $holding->coin->coin_id;

        // Try getting price from cache first
        $cachedPrices = Cache::get("coin_prices_user_$userId", []);
        $currentPrice = $cachedPrices[$coinIdApi] ?? null;

        // Fallback to API if not found in cache
        if (!$currentPrice) {
            $coinGecko = app(CoinGeckoService::class);
            $fallbackPrices = $coinGecko->getPrices([$coinIdApi]);
            $currentPrice = $fallbackPrices[$coinIdApi] ?? null;
        }

        if (!$currentPrice) {
            return redirect()->back()->with('status', 'Unable to fetch current price.');
        }

        // Calculate sale
        $soldAmount = $holding->amount;
        $valueUsd = $soldAmount * $currentPrice;
        $averageBuyPrice = $holding->average_buy_price;
        $realizedPnl = ($currentPrice - $averageBuyPrice) * $soldAmount;

        // Record transaction
        Transaction::create([
            'user_id' => $userId,
            'coin_id' => $coinId,
            'amount' => $soldAmount,
            'price' => $currentPrice,
            'value_usd' => $valueUsd,
            'type' => 'sell',
        ]);

        // Update user balance and realized PnL
        $user->balance += $valueUsd;
        $user->save();

        $profitRow = RealizedPnl::firstOrNew(['user_id' => $userId]);
        $profitRow->total_realized_pnl += $realizedPnl;
        $profitRow->save();

        // Delete holding
        $holding->delete();

        return redirect()->back();
    }
}
