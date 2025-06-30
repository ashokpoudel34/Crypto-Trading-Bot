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

<form method="POST" action="{{ route('user.toggleAutoTrade') }}" class="mb-6">
    @csrf
    <label class="inline-flex items-center cursor-pointer">
        <input 
            type="checkbox" 
            name="auto_trade_enabled" 
            class="sr-only peer" 
            onchange="this.form.submit()"
            {{ auth()->user()->auto_trade_enabled ? 'checked' : '' }}
        >
        <div class="relative w-11 h-6 bg-red-500 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-500"></div>
        <span class="ml-3 text-sm font-medium text-gray-900">Auto Trading</span>
    </label>
</form>



{{-- Portfolio Summary --}}
<h4 class="text-lg font-semibold mb-3">Portfolio Summary</h4>
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <div class="bg-gray-100 p-4 rounded-lg shadow">
        <h5 class="font-medium">Total Value</h5>
        <p class="text-xl">${{ number_format($totalValue, 2) }}</p>
    </div>
    <div class="bg-gray-100 p-4 rounded-lg shadow">
        <h5 class="font-medium">Unrealized P/L</h5>
        <p class="text-xl @if($totalUnRealizedPnl >= 0) text-green-600 @else text-red-600 @endif">
            ${{ number_format($totalUnRealizedPnl, 6) }}
        </p>
    </div>
    <div class="bg-gray-100 p-4 rounded-lg shadow">
        <h5 class="font-medium">Realized P/L</h5>
        <p class="text-xl @if($totalRealizedPnl >= 0) text-green-600 @else text-red-600 @endif">
            ${{ number_format($totalRealizedPnl, 6) }}
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
                        <div class="flex items-center justify-between space-x-2">
                            <span>${{ number_format($item->profit_loss, 2) }}</span>
                            <form method="POST" action="{{ route('user.sell', $item->coin->id) }}">
                                @csrf
                                <button type="submit" class="px-2 py-1 rounded text-xs text-white transition {{ $item->profit_loss >= 0 ? 'bg-green-500 hover:bg-green-600' : 'bg-red-500 hover:bg-red-600' }}">
                                    Sell Now
                                </button>
                            </form>
                        </div>
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
