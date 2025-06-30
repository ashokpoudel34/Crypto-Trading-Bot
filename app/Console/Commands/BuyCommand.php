<?php

namespace App\Console\Commands;

use App\Models\Coin;
use App\Models\User;
use App\Models\Portfolio;
use App\Models\Transaction;
use Illuminate\Console\Command;
use App\Services\CoinGeckoService;
use App\Notifications\CoinPurchased;

class BuyCommand extends Command
{
    protected $signature = 'trading:buy';
    protected $description = 'Execute buy orders based on 5% dip over 7 days';

public function handle(CoinGeckoService $coingecko)
{
    $coins = Coin::all(); // DB model
    $users = User::all();

$coinIds = $coins->pluck('coin_id')->toArray(); // 283 CoinGecko IDs
echo "Total coins sent: " . count($coinIds) . "\n";

$allData = [];
$chunks = array_chunk($coinIds, 100);

foreach ($chunks as $chunk) {
    $data = $coingecko->getSevenDayChanges($chunk); // Pass 100 IDs per request
    $allData = array_merge($allData, $data);
    sleep(1); // optional: be kind to the API
}

echo "Total coins returned: " . count($allData) . "\n";

    foreach ($users as $user) {
        $purchases = [];
        foreach ($allData as $coin) {
            $changePercentage = $coin['price_change_percentage_7d_in_currency'];
            if (!is_numeric($changePercentage)) {
            continue;
            }
            $currentPrice = $coin['current_price'];

            if ($changePercentage <= -5) {
                // Match CoinGecko ID to DB ID
                $coinModel = $coins->firstWhere('coin_id', $coin['id']);
                if (!$coinModel) continue;

                $amount = 5 / $currentPrice;

                Transaction::create([
                    'user_id' => $user->id,
                    'coin_id' => $coinModel->id,
                    'type' => 'buy',
                    'amount' => $amount,
                    'price' => $currentPrice,
                    'value_usd' => 5
                ]);

                // Update portfolio
                $portfolio = Portfolio::firstOrNew([
                    'user_id' => $user->id,
                    'coin_id' => $coinModel->id
                ]);

                if ($portfolio->exists) {
                    $totalAmount = $portfolio->amount + $amount;
                    $totalValue = ($portfolio->average_buy_price * $portfolio->amount) + 5;
                    $portfolio->average_buy_price = $totalValue / $totalAmount;
                    $portfolio->amount = $totalAmount;
                } else {
                    $portfolio->amount = $amount;
                    $portfolio->average_buy_price = $currentPrice;
                }

                $portfolio->save();
                print($changePercentage . " " . $coin['id'] . "\n");

                $purchases[] = [
                'name' => $coin['name'],
                'symbol' => $coin['symbol'],
                'amount' => $amount,
                'price' => $currentPrice,
                'change' => $changePercentage,
            ];

            }
        }
        if (!empty($purchases)) {
            $user->notify(new CoinPurchased($purchases));
        }
    }

    $this->info('Buy orders processed successfully');
}

}
