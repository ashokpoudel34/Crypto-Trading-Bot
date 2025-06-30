<!-- resources/views/dashboard.blade.php -->

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Session Alert --}}
            @if (session('status'))
                <div class="mb-4 font-medium text-sm text-green-600">
                    {{ session('status') }}
                </div>
            @endif

            {{-- Portfolio Summary --}}
            <h4 class="text-lg font-semibold mb-3">Portfolio Summary</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="bg-gray-100 p-4 rounded-lg shadow">
                    <h5 class="font-medium">Total Value</h5>
                    <p class="text-xl">${{ number_format($totalValue, 2) }}</p>
                </div>
                <div class="bg-gray-100 p-4 rounded-lg shadow">
                    <h5 class="font-medium">Profit/Loss</h5>
                    <p class="text-xl @if($totalProfitLoss >= 0) text-green-600 @else text-red-600 @endif">
                        ${{ number_format($totalProfitLoss, 2) }}
                    </p>
                </div>
            </div>

            {{-- Holdings --}}
            <h4 class="text-lg font-semibold mb-3">Your Holdings</h4>
            <div class="overflow-x-auto mb-6">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left font-semibold">Coin</th>
                            <th class="px-4 py-2 text-left font-semibold">Amount</th>
                            <th class="px-4 py-2 text-left font-semibold">Avg Buy Price</th>
                            <th class="px-4 py-2 text-left font-semibold">Current Price</th>
                            <th class="px-4 py-2 text-left font-semibold">Value</th>
                            <th class="px-4 py-2 text-left font-semibold">P/L</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($portfolio as $item)
                            <tr>
                                <td class="px-4 py-2">{{ $item->coin->name }} ({{ $item->coin->symbol }})</td>
                                <td class="px-4 py-2">{{ number_format($item->amount, 4) }}</td>
                                <td class="px-4 py-2">${{ number_format($item->average_buy_price, 6) }}</td>
                                <td class="px-4 py-2">${{ number_format($item->current_price, 6) }}</td>
                                <td class="px-4 py-2">${{ number_format($item->current_value, 2) }}</td>
                                <td class="px-4 py-2 @if($item->profit_loss >= 0) text-green-600 @else text-red-600 @endif">
                                    ${{ number_format($item->profit_loss, 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Recent Transactions --}}
            <h4 class="text-lg font-semibold mb-3">Recent Transactions</h4>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left font-semibold">Date</th>
                            <th class="px-4 py-2 text-left font-semibold">Type</th>
                            <th class="px-4 py-2 text-left font-semibold">Coin</th>
                            <th class="px-4 py-2 text-left font-semibold">Amount</th>
                            <th class="px-4 py-2 text-left font-semibold">Price</th>
                            <th class="px-4 py-2 text-left font-semibold">Value</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($transactions as $tx)
                            <tr>
                                <td class="px-4 py-2">{{ $tx->created_at->format('m/d/Y H:i') }}</td>
                                <td class="px-4 py-2 @if($tx->type == 'buy') text-green-600 @else text-red-600 @endif">
                                    {{ strtoupper($tx->type) }}
                                </td>
                                <td class="px-4 py-2">{{ $tx->coin->name }} ({{ $tx->coin->symbol }})</td>
                                <td class="px-4 py-2">{{ number_format($tx->amount, 4) }}</td>
                                <td class="px-4 py-2">${{ number_format($tx->price, 6) }}</td>
                                <td class="px-4 py-2">${{ number_format($tx->value_usd, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</x-app-layout>
