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
        $users = User::where('auto_trade_enabled', true)->get();
        if ($users->isEmpty()) {
            $this->info('No users with auto-trading enabled');
        return 0; // Exit code for success
        }

        // Collect unique CoinGecko IDs from portfolios
        $coinIds = $portfolios->pluck('coin.coin_id')->unique()->values()->toArray();

        $allData = [];
        $chunks = array_chunk($coinIds, 100);

        foreach ($chunks as $chunk) {
            $data = $coingecko->getSevenDayChanges($chunk);
            $allData = array_merge($allData, $data);
            sleep(1); // optional: to avoid rate limits
        }

        // Index the data by CoinGecko ID for quick lookup
        $priceData = collect($allData)->keyBy('id');

        // Group portfolios by user_id to process user portfolios separately
        $portfoliosByUser = $portfolios->groupBy('user_id');

        foreach ($users as $user) {
            $solddata = [];
            $userPortfolios = $portfoliosByUser->get($user->id, collect());

            foreach ($userPortfolios as $portfolio) {
                $coinId = $portfolio->coin->coin_id;

                if (!isset($priceData[$coinId])) {
                    $this->warn("No data for coin ID: {$coinId}");
                    continue;
                }

                $market = $priceData[$coinId];
                $changePercentage = $market['price_change_percentage_7d_in_currency'];
                $currentPrice = $market['current_price'];

                // Sell if price change is >= 10% AND user holds some amount
                if ($changePercentage >= 10 || $portfolio->amount > 0) {
                    // Sell $10 worth or remaining amount, whichever is smaller
                    $amountToSell = min(10 / $currentPrice, $portfolio->amount);
                    $valueUsd = $amountToSell * $currentPrice;

                    // Calculate realized profit/loss
                    $costBasis = $amountToSell * $portfolio->average_buy_price;
                    $realizedProfit = $valueUsd - $costBasis;

                    // Create a sell transaction
                    Transaction::create([
                        'user_id' => $user->id,
                        'coin_id' => $portfolio->coin_id,
                        'type' => 'sell',
                        'amount' => $amountToSell,
                        'price' => $currentPrice,
                        'value_usd' => $valueUsd
                    ]);

                    // Update or remove portfolio
                    $portfolio->amount -= $amountToSell;
                    if ($portfolio->amount <= 0) {
                        $portfolio->delete();
                    } else {
                        $portfolio->save();
                    }

                    // Update realized profits
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
