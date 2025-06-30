<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Portfolio;
use App\Models\RealizedPnl;
use App\Models\Transaction;
use App\Notifications\CoinSold;
use Illuminate\Console\Command;
use App\Services\CoinGeckoService;

class SellCommand extends Command
{
    protected $signature = 'trading:sell';
    protected $description = 'Execute sell orders based on 10% gain over 7 days';

 public function handle(CoinGeckoService $coingecko)
{
    $portfolios = Portfolio::with('coin', 'user')->get();
    $users = User::all();

    // Collect unique CoinGecko IDs from portfolios
    $coinIds = $portfolios->pluck('coin.coin_id')->unique()->values()->toArray();

    $allData = [];
    $chunks = array_chunk($coinIds, 100);

    foreach ($chunks as $chunk) {
        $data = $coingecko->getSevenDayChanges($chunk);
        $allData = array_merge($allData, $data);
        sleep(1); // optional: to avoid rate limits
    }

    // Index the data by CoinGecko ID
    $priceData = collect($allData)->keyBy('id');

    foreach ($users as $user) {
    $solddata = [];
    foreach ($portfolios as $portfolio) {
        $coinId = $portfolio->coin->coin_id;
        $user = $portfolio->user;

        if (!isset($priceData[$coinId])) {
            $this->warn("No data for {$coinId}");
            continue;
        }

        $market = $priceData[$coinId];
        $changePercentage = $market['price_change_percentage_7d_in_currency'];
        $currentPrice = $market['current_price'];

        if ($changePercentage >= 10 || $portfolio->amount > 0) {
            // Sell $10 worth or remaining amount
$amountToSell = min(10 / $currentPrice, $portfolio->amount);
$valueUsd = $amountToSell * $currentPrice;

// Calculate realized PnL
$costBasis = $amountToSell * $portfolio->average_buy_price;
$realizedProfit = $valueUsd - $costBasis;

// Save transaction
Transaction::create([
    'user_id' => $user->id,
    'coin_id' => $portfolio->coin_id,
    'type' => 'sell',
    'amount' => $amountToSell,
    'price' => $currentPrice,
    'value_usd' => $valueUsd
]);

// Update or delete portfolio
$portfolio->amount -= $amountToSell;
if ($portfolio->amount <= 0) {
    $portfolio->delete();
} else {
    $portfolio->save();
}

// Update or insert into realized_profits table
$profitRow = RealizedPnl::firstOrNew(['user_id' => $user->id]);
$profitRow->total_realized_pnl += $realizedProfit;
$profitRow->save();


            $solddata[] = [
                'name' => $market['name'],
                'symbol' => $market['symbol'],
                'amount' => $amountToSell,
                'price' => $currentPrice,
                'change' => $changePercentage,
            ];


            $this->info("Sold {$amountToSell} of {$coinId} at \${$currentPrice} for user {$user->id}");
        }
    }
        if (!empty($solddata)) {
            $user->notify(new CoinSold($solddata));
        }

}

    $this->info('Sell orders processed successfully');
}

}
