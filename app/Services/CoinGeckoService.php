<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class CoinGeckoService
{
    /**
     * Create a new class instance.
     */
    protected $client;
    
    public function __construct()
    {
        $this->client = new \GuzzleHttp\Client([
            'base_uri' => 'https://api.coingecko.com/api/v3/',
            'timeout'  => 10,
        ]);
    }

    public function getCoinList()
    {
        return Cache::remember('coin-list', now()->addHours(24), function () {
            $response = $this->client->get('coins/list');
            return json_decode($response->getBody(), true);
        });
    }

    public function getCoinPrice(string $coinId, string $vsCurrency = 'usd')
    {
        $cacheKey = "coin-price-{$coinId}-{$vsCurrency}";
        
        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($coinId, $vsCurrency) {
            $response = $this->client->get("simple/price?ids={$coinId}&vs_currencies={$vsCurrency}");
            $data = json_decode($response->getBody(), true);
            return $data[$coinId][$vsCurrency] ?? null;
        });
    }

    public function getPrices(array $coinIds)
{
    $response = Http::get("https://api.coingecko.com/api/v3/simple/price", [
        'ids' => implode(',', $coinIds),
        'vs_currencies' => 'usd',
    ]);

    if ($response->successful()) {
        return collect($response->json())->mapWithKeys(function ($item, $key) {
            return [$key => $item['usd']];
        })->toArray();
    }

    return [];
}


public function getSevenDayChanges(array $coinIds)
{
    $cacheKey = 'seven-day-change-' . md5(implode(',', $coinIds));
    
    return Cache::remember($cacheKey, now()->addMinutes(30), function () use ($coinIds) {
        $ids = implode(',', $coinIds);
$response = $this->client->get('coins/markets', [
    'query' => [
        'vs_currency' => 'usd',
        'ids' => $ids,
        'price_change_percentage' => '7d'
    ]
]);

        return json_decode($response->getBody(), true);
    });
}

}
