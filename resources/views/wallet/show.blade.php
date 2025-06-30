<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Wallet') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Status Alert --}}
            @if (session('status'))
                <div class="mb-4 rounded-md bg-green-50 p-4 text-green-700">
                    {{ session('status') }}
                </div>
            @endif

            {{-- Wallet Balance --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-8">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 mb-3">Current Balance</h3>
                    <p class="text-3xl font-extrabold text-gray-900">
                        ${{ number_format($user->balance, 2) }}
                    </p>
                </div>
            </div>

            {{-- Reload Balance Form --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 mb-5">Reload Balance</h3>
                    <form action="{{ route('wallet.reload') }}" method="POST" class="space-y-6">
                        @csrf
                        <div>
                            <label for="amount" class="block text-sm font-medium text-gray-700">Amount (USD)</label>
                            <input
                                type="number"
                                name="amount"
                                id="amount"
                                min="1"
                                step="0.01"
                                required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            >
                            @error('amount')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <button
                                type="submit"
                                class="inline-flex justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                            >
                                Reload Balance
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
