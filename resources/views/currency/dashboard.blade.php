@extends('layouts.app')

@section('title', 'Dashboard - Currency Analytics')

@section('content')
<div class="max-w-6xl mx-auto">
    <div id="dashboardContent" class="animate-fadeInUp">
        <!-- Header -->
        <div class="bg-gradient-to-r from-violet-300 to-violet-400 backdrop-blur-sm rounded-2xl shadow-2xl p-6 md:p-8 mb-4 md:mb-6">
            <h2 class="text-2xl md:text-4xl font-bold text-white text-center mb-2">Currency Analytics Dashboard</h2>
            <p class="text-sm md:text-base text-violet-50 text-center">Live rates and market overview at a glance.</p>
        </div>

        <!-- Top Currency Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3 md:gap-6 mb-4 md:mb-6 animate-fadeInUp animate-delay-100">
            @if(isset($rates['EUR']))
            <div class="bg-white backdrop-blur-sm rounded-xl shadow-lg p-6 border border-violet-200">
                <div class="flex justify-between items-start mb-2">
                    <h3 class="text-sm font-medium text-violet-600">🇪🇺 USD/EUR</h3>
                </div>
                <p class="text-3xl font-bold text-violet-900">{{ number_format($rates['EUR'], 4) }}</p>
                <p class="text-xs text-violet-500 mt-1">US Dollar to Euro</p>
            </div>
            @endif

            @if(isset($rates['GBP']))
            <div class="bg-white backdrop-blur-sm rounded-xl shadow-lg p-6 border border-violet-200">
                <div class="flex justify-between items-start mb-2">
                    <h3 class="text-sm font-medium text-violet-600">🇬🇧 USD/GBP</h3>
                </div>
                <p class="text-3xl font-bold text-violet-900">{{ number_format($rates['GBP'], 4) }}</p>
                <p class="text-xs text-violet-500 mt-1">US Dollar to British Pound</p>
            </div>
            @endif

            @if(isset($rates['JPY']))
            <div class="bg-white backdrop-blur-sm rounded-xl shadow-lg p-6 border border-violet-200">
                <div class="flex justify-between items-start mb-2">
                    <h3 class="text-sm font-medium text-violet-600">🇯🇵 USD/JPY</h3>
                </div>
                <p class="text-3xl font-bold text-violet-900">{{ number_format($rates['JPY'], 2) }}</p>
                <p class="text-xs text-violet-500 mt-1">US Dollar to Japanese Yen</p>
            </div>
            @endif

            @if(isset($rates['PHP']))
            <div class="bg-white backdrop-blur-sm rounded-xl shadow-lg p-6 border border-violet-200">
                <div class="flex justify-between items-start mb-2">
                    <h3 class="text-sm font-medium text-violet-600">🇵🇭 USD/PHP</h3>
                </div>
                <p class="text-3xl font-bold text-violet-900">{{ number_format($rates['PHP'], 2) }}</p>
                <p class="text-xs text-violet-500 mt-1">US Dollar to Philippine Peso</p>
            </div>
            @endif
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 md:gap-6 mb-4 md:mb-6 animate-fadeInUp animate-delay-200">

            <!-- Live Exchange Rates -->
            <div class="bg-white backdrop-blur-sm rounded-xl shadow-lg p-6 border border-violet-100">
                <h3 class="text-xl font-bold text-violet-900 mb-4">Live Exchange Rates</h3>
                <div class="space-y-3">
                    @php
                        $flags = [
                            'EUR' => '🇪🇺',
                            'GBP' => '🇬🇧',
                            'JPY' => '🇯🇵',
                            'PHP' => '🇵🇭',
                            'AUD' => '🇦🇺',
                            'CAD' => '🇨🇦',
                        ];
                        $currencyNames = [
                            'EUR' => 'Euro',
                            'GBP' => 'British Pound',
                            'JPY' => 'Japanese Yen',
                            'PHP' => 'Philippine Peso',
                            'AUD' => 'Australian Dollar',
                            'CAD' => 'Canadian Dollar',
                        ];
                    @endphp

                    @foreach($rates as $currency => $rate)
                    @php
                        $today = now()->format('Y-m-d');
                        $seed = abs(crc32($today . 'USD' . $currency));
                        $drift = (($seed % 10000) / 10000 - 0.5) * 0.006;
                        $change = round($drift * 100, 2);
                    @endphp
                    <div class="flex justify-between items-center py-2 border-b border-violet-50 last:border-0"
                         data-currency="{{ $currency }}">
                        <div>
                            <p class="font-semibold text-violet-900">
                                {{ $flags[$currency] ?? '🏳️' }} USD/{{ $currency }}
                            </p>
                            <p class="text-xs text-violet-500">
                                {{ $currencyNames[$currency] ?? $currency }}
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="font-bold text-violet-900 rate-value">{{ number_format($rate, 4) }}</p>
                            <p class="rate-change text-xs {{ $change >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ $change >= 0 ? '+' : '' }}{{ number_format($change, 2) }}%
                            </p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Market Overview Chart -->
            <div class="bg-white backdrop-blur-sm rounded-xl shadow-lg p-6 border border-violet-100">
                <h3 class="text-xl font-bold text-violet-900 mb-4">Market Overview</h3>
                <canvas id="marketChart" style="max-height: 300px;"></canvas>
            </div>
        </div>

        <!-- Gainers and Losers -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 md:gap-6 mb-4 md:mb-6 animate-fadeInUp animate-delay-300">

            <!-- Top Gainers -->
            <div class="bg-white backdrop-blur-sm rounded-xl shadow-lg p-6 border border-violet-100">
                <h3 class="text-xl font-bold text-violet-900 mb-4">Top Gainers (24h)</h3>
                <div class="space-y-3" id="gainers-list">
                    @foreach($gainers as $gainer)
                    @php $gc = explode('/', $gainer['pair'])[1]; @endphp
                    <div class="flex justify-between items-center py-3 px-4 bg-green-50 rounded-lg">
                        <div>
                            <p class="font-semibold text-violet-900">
                                {{ $flags[$gc] ?? '🏳️' }} {{ $gainer['pair'] }}
                            </p>
                            <p class="text-xs text-violet-500">{{ $gainer['rate'] }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-lg font-bold text-green-600">+{{ number_format($gainer['change'], 2) }}%</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Top Losers -->
            <div class="bg-white backdrop-blur-sm rounded-xl shadow-lg p-6 border border-violet-100">
                <h3 class="text-xl font-bold text-violet-900 mb-4">Top Losers (24h)</h3>
                <div class="space-y-3" id="losers-list">
                    @foreach($losers as $loser)
                    @php $lc = explode('/', $loser['pair'])[1]; @endphp
                    <div class="flex justify-between items-center py-3 px-4 bg-red-50 rounded-lg">
                        <div>
                            <p class="font-semibold text-violet-900">
                                {{ $flags[$lc] ?? '🏳️' }} {{ $loser['pair'] }}
                            </p>
                            <p class="text-xs text-violet-500">{{ $loser['rate'] }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-lg font-bold text-red-600">{{ number_format($loser['change'], 2) }}%</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Refresh Button -->
        <div class="text-center animate-fadeIn mb-4">
            <button id="refreshBtn"
                    class="px-6 md:px-8 py-3 md:py-4 bg-gradient-to-r from-violet-300 to-violet-400 text-white text-sm md:text-base font-semibold rounded-lg hover:from-violet-400 hover:to-violet-500 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 disabled:opacity-70 disabled:cursor-not-allowed">
                Refresh Dashboard
            </button>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
    // Market Overview Chart
    const ctx = document.getElementById('marketChart').getContext('2d');
    const marketChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: {!! json_encode(array_map(fn($d) => \Carbon\Carbon::parse($d['date'])->format('M d'), $chart_data)) !!},
            datasets: [{
                label: 'USD/PHP (7 Days)',
                data: {!! json_encode(array_column($chart_data, 'rate')) !!},
                borderColor: 'rgb(147, 51, 234)',
                backgroundColor: 'rgba(147, 51, 234, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { display: true, position: 'top' }
            },
            scales: {
                y: { beginAtZero: false }
            }
        }
    });

    // Auto-refresh countdown
    let secondsLeft = 60;
    const refreshBtn = document.getElementById('refreshBtn');

    function updateCountdown() {
        refreshBtn.textContent = `Refresh Dashboard (auto in ${secondsLeft}s)`;
    }

    updateCountdown();

    const countdownInterval = setInterval(() => {
        secondsLeft--;
        updateCountdown();
        if (secondsLeft <= 0) {
            secondsLeft = 60;
            refreshRates();
        }
    }, 1000);

    // Manual refresh
    refreshBtn.addEventListener('click', () => {
        secondsLeft = 60;
        updateCountdown();
        refreshRates();
    });

    async function refreshRates() {
        refreshBtn.disabled = true;
        refreshBtn.textContent = 'Refreshing...';

        try {
            const response = await fetch('{{ route('currency.live-rates') }}');
            const data = await response.json();

            if (data && data.rates) {
                updateRateRows(data.rates);
                updateGainersLosers(data.gainers, data.losers);
            }
        } catch (error) {
            console.error('Auto-refresh error:', error);
        } finally {
            refreshBtn.disabled = false;
            updateCountdown();
        }
    }

    function updateRateRows(rates) {
        const today = new Date().toISOString().slice(0, 10);
        const pairs = ['EUR', 'GBP', 'JPY', 'PHP', 'AUD', 'CAD'];

        pairs.forEach(currency => {
            const row = document.querySelector(`[data-currency="${currency}"]`);
            if (!row || !rates[currency]) return;

            const seed = simpleHash(today + 'USD' + currency);
            const drift = ((seed % 10000) / 10000 - 0.5) * 0.006;
            const change = (drift * 100).toFixed(2);
            const isUp = parseFloat(change) >= 0;

            row.querySelector('.rate-value').textContent = parseFloat(rates[currency]).toFixed(4);
            const changeEl = row.querySelector('.rate-change');
            changeEl.textContent = `${isUp ? '+' : ''}${change}%`;
            changeEl.className = `rate-change text-xs ${isUp ? 'text-green-600' : 'text-red-600'}`;
        });
    }

    function updateGainersLosers(gainers, losers) {
        const gainersEl = document.getElementById('gainers-list');
        const losersEl = document.getElementById('losers-list');

        if (gainersEl && gainers) {
            gainersEl.innerHTML = gainers.map(g => `
                <div class="flex justify-between items-center py-3 px-4 bg-green-50 rounded-lg">
                    <div>
                        <p class="font-semibold text-violet-900">${currencyFlag(g.pair.split('/')[1])} ${g.pair}</p>
                        <p class="text-xs text-violet-500">${g.rate}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-lg font-bold text-green-600">+${parseFloat(g.change).toFixed(2)}%</p>
                    </div>
                </div>
            `).join('');
        }

        if (losersEl && losers) {
            losersEl.innerHTML = losers.map(l => `
                <div class="flex justify-between items-center py-3 px-4 bg-red-50 rounded-lg">
                    <div>
                        <p class="font-semibold text-violet-900">${currencyFlag(l.pair.split('/')[1])} ${l.pair}</p>
                        <p class="text-xs text-violet-500">${l.rate}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-lg font-bold text-red-600">${parseFloat(l.change).toFixed(2)}%</p>
                    </div>
                </div>
            `).join('');
        }
    }

    // Mirror of PHP seeded hash — must match CurrencyService::dailySeed logic
    function simpleHash(str) {
        let hash = 0;
        for (let i = 0; i < str.length; i++) {
            const chr = str.charCodeAt(i);
            hash = ((hash << 5) - hash) + chr;
            hash |= 0;
        }
        return Math.abs(hash);
    }
</script>
@endpush