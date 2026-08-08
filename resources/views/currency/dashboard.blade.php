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
                <h3 class="text-sm font-medium text-violet-600 mb-2">🇪🇺 USD/EUR</h3>
                <p class="text-3xl font-bold text-violet-900">{{ number_format($rates['EUR'], 4) }}</p>
                <p class="text-xs text-violet-500 mt-1">US Dollar to Euro</p>
            </div>
            @endif

            @if(isset($rates['GBP']))
            <div class="bg-white backdrop-blur-sm rounded-xl shadow-lg p-6 border border-violet-200">
                <h3 class="text-sm font-medium text-violet-600 mb-2">🇬🇧 USD/GBP</h3>
                <p class="text-3xl font-bold text-violet-900">{{ number_format($rates['GBP'], 4) }}</p>
                <p class="text-xs text-violet-500 mt-1">US Dollar to British Pound</p>
            </div>
            @endif

            @if(isset($rates['JPY']))
            <div class="bg-white backdrop-blur-sm rounded-xl shadow-lg p-6 border border-violet-200">
                <h3 class="text-sm font-medium text-violet-600 mb-2">🇯🇵 USD/JPY</h3>
                <p class="text-3xl font-bold text-violet-900">{{ number_format($rates['JPY'], 2) }}</p>
                <p class="text-xs text-violet-500 mt-1">US Dollar to Japanese Yen</p>
            </div>
            @endif

            @if(isset($rates['PHP']))
            <div class="bg-white backdrop-blur-sm rounded-xl shadow-lg p-6 border border-violet-200">
                <h3 class="text-sm font-medium text-violet-600 mb-2">🇵🇭 USD/PHP</h3>
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
                        $today  = now()->format('Y-m-d');
                        $seed   = abs(crc32($today . 'USD' . $currency));
                        $drift  = (($seed % 10000) / 10000 - 0.5) * 0.006;
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
                <h3 class="text-xl font-bold text-violet-900 mb-1">Market Overview</h3>
                <p class="text-xs text-violet-400 mb-3">USD/PHP — Last 7 days</p>
                <canvas id="marketChart" style="max-height: 260px;"></canvas>
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
    // ── Market Overview Chart — calls service directly, no variable dependency ──
    @php
        $phpRates = app(\App\Services\CurrencyService::class)->getHistoricalData('USD', 'PHP', 6);
    @endphp

    (function() {
        const labels = {!! json_encode(array_map(fn($d) => \Carbon\Carbon::parse($d['date'])->format('M d'), $phpRates)) !!};
        const values = {!! json_encode(array_column($phpRates, 'rate')) !!};
        const ctx    = document.getElementById('marketChart').getContext('2d');

        window.marketChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'USD/PHP',
                    data: values,
                    borderColor: 'rgb(147, 51, 234)',
                    backgroundColor: 'rgba(147, 51, 234, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointRadius: 3,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        labels: { boxWidth: 12, font: { size: 11 } }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 10 } }
                    },
                    y: {
                        beginAtZero: false,
                        grid: { color: 'rgba(139, 92, 246, 0.08)' },
                        ticks: { font: { size: 10 } }
                    }
                }
            }
        });
    })();

    // Manual refresh only — removed automatic polling to respect rate limits
    const refreshBtn = document.getElementById('refreshBtn');

    refreshBtn.addEventListener('click', () => {
        refreshBtn.disabled = true;
        refreshBtn.textContent = 'Refreshing...';
        refreshRates();
    });

    async function refreshRates() {
        try {
            const response = await fetch('{{ route('currency.live-rates') }}', {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const contentType = response.headers.get('content-type') || '';

            if (response.status === 429) {
                let body = {};
                if (contentType.includes('application/json')) {
                    body = await response.json().catch(() => ({}));
                }
                const retry = body.retry_after ? ` Try again in ${body.retry_after} seconds.` : '';
                Swal.fire({ icon: 'warning', title: 'Too Many Requests', text: (body.message || 'Rate limit exceeded.') + retry, timer: 4000 });
                return;
            }

            let data;
            if (contentType.includes('application/json')) {
                data = await response.json();
            } else {
                const text = await response.text();
                console.error('Unexpected response when refreshing:', text);
                Swal.fire({ icon: 'error', title: 'Refresh Failed', text: 'Unexpected server response.', timer: 3000 });
                return;
            }

            if (data && data.rates) {
                updateRateRows(data.rates);
                updateGainersLosers(data.gainers, data.losers);

                // Update market chart (append latest USD/PHP value)
                try {
                    const phpRate = parseFloat(data.rates['PHP']);
                    if (!isNaN(phpRate) && window.marketChart) {
                        const nowLabel = new Date().toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
                        const ds = window.marketChart.data.datasets[0];
                        window.marketChart.data.labels.push(nowLabel);
                        ds.data.push(phpRate);
                        // keep length consistent with initial data
                        if (window.marketChart.data.labels.length > {!! count($phpRates) !!}) {
                            window.marketChart.data.labels.shift();
                            ds.data.shift();
                        }
                        window.marketChart.update();
                    }
                } catch (chartError) {
                    console.error('Market chart update error:', chartError);
                }

                // Show a centered success modal indicating refresh completed
                try {
                    const updatedCount = Object.keys(data.rates).length;
                    Swal.fire({
                        icon: 'success',
                        title: 'Dashboard Refreshed',
                        text: `Updated ${updatedCount} rates successfully.`,
                        showConfirmButton: false,
                        timer: 1800
                    });
                } catch (swalErr) {
                    // ignore
                }
            }
        } catch (error) {
            console.error('Refresh error:', error);
            Swal.fire({ icon: 'error', title: 'Refresh Failed', text: 'Could not refresh dashboard. Please try again.', timer: 3000 });
        } finally {
            refreshBtn.disabled = false;
            refreshBtn.textContent = 'Refresh Dashboard';
        }
    }

    function updateRateRows(rates) {
        const today = new Date().toISOString().slice(0, 10);
        const pairs = ['EUR', 'GBP', 'JPY', 'PHP', 'AUD', 'CAD'];

        pairs.forEach(currency => {
            const row = document.querySelector(`[data-currency="${currency}"]`);
            if (!row || !rates[currency]) return;

            const seed   = simpleHash(today + 'USD' + currency);
            const drift  = ((seed % 10000) / 10000 - 0.5) * 0.006;
            const change = (drift * 100).toFixed(2);
            const isUp   = parseFloat(change) >= 0;

            row.querySelector('.rate-value').textContent = parseFloat(rates[currency]).toFixed(4);
            const changeEl = row.querySelector('.rate-change');
            changeEl.textContent = `${isUp ? '+' : ''}${change}%`;
            changeEl.className   = `rate-change text-xs ${isUp ? 'text-green-600' : 'text-red-600'}`;
        });
    }

    function updateGainersLosers(gainers, losers) {
        const gainersEl = document.getElementById('gainers-list');
        const losersEl  = document.getElementById('losers-list');

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