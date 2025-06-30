<?php

namespace App\Console\Commands;

use App\Models\Coin;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class ImportTopCoins extends Command
{
    protected $signature = 'coins:import-top';
    protected $description = 'Import top coins with high volume and market cap from CoinGecko';

public function handle()
{
    $this->info("Fetching top coins from CoinGecko...");

    $page = 1;
    $validCoinIds = [];

    while (true) {
        try {
        $response = Http::withHeaders([
            'x-cg-pro-api-key' => env('COINGECKO_API_KEY'), // <-- set in your .env
                ])->get('https://api.coingecko.com/api/v3/coins/markets', [
            'vs_currency' => 'usd',
            'order' => 'market_cap_desc',
            'per_page' => 250,
            'page' => $page,
            'sparkline' => false,
            'price_change_percentage' => '7d',
        ]);

            // Handle rate limit
            if ($response->status() === 429) {
                $this->warn("Rate limit hit on page $page. Waiting 60 seconds...");
                sleep(60);
                continue; // retry the same page
            }

            if ($response->failed()) {
                $this->error("Failed to fetch data from page $page. HTTP Status: " . $response->status());
                break;
            }

            $coins = $response->json();

            if (empty($coins)) {
                break;
            }

            foreach ($coins as $coin) {
                if (($coin['total_volume'] ?? 0) < 10000000 || ($coin['market_cap'] ?? 0) < 80000000) {
                    continue;
                }

                Coin::updateOrCreate(
                    ['coin_id' => $coin['id']],
                    [
                        'symbol' => $coin['symbol'],
                        'name'   => $coin['name']
                    ]
                );

                $validCoinIds[] = $coin['id'];
            }

            $this->info("Processed page $page");
            $page++;
            //sleep(6); // delay between requests to avoid hitting the limit
        } catch (\Exception $e) {
            $this->error("Exception on page $page: " . $e->getMessage());
            sleep(60); // fallback delay
        }
    }

    // Delete coins not in the current list
    $deleted = Coin::whereNotIn('coin_id', $validCoinIds)->delete();
    $this->info("Deleted $deleted low-volume coins.");

    $this->info("Top coins imported successfully.");
    return 0;
}

}
