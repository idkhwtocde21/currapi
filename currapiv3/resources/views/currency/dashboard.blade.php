@extends('layouts.app')

@section('title', 'Dashboard - Currency Analytics')

@section('content')
<div class="max-w-6xl mx-auto">
    <div id="dashboardContent" class="animate-fadeInUp">
        <!-- Header -->
        <div class="bg-gradient-to-r from-violet-300 to-violet-400 backdrop-blur-sm rounded-2xl shadow-2xl p-6 md:p-8 mb-4 md:mb-6">
            <h2 class="text-2xl md:text-4xl font-bold text-white text-center mb-2">Currency Analytics Dashboard</h2>
            <p class="text-sm md:text-base text-violet-50 text-center">Overview of global currency markets</p>
        </div>

        <!-- Top Currency Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3 md:gap-6 mb-4 md:mb-6 animate-fadeInUp animate-delay-100">
            @if(isset($rates['EUR']))
            <div class="bg-white backdrop-blur-sm rounded-xl shadow-lg p-6 border border-violet-200">
                <div class="flex justify-between items-start mb-2">
                    <h3 class="text-sm font-medium text-violet-600">USD/EUR</h3>
                </div>
                <p class="text-3xl font-bold text-violet-900">{{ number_format($rates['EUR'], 4) }}</p>
                <p class="text-xs text-violet-500 mt-1">US Dollar to Euro</p>
            </div>
            @endif

            @if(isset($rates['GBP']))
            <div class="bg-white backdrop-blur-sm rounded-xl shadow-lg p-6 border border-violet-200">
                <div class="flex justify-between items-start mb-2">
                    <h3 class="text-sm font-medium text-violet-600">USD/GBP</h3>
                </div>
                <p class="text-3xl font-bold text-violet-900">{{ number_format($rates['GBP'], 4) }}</p>
                <p class="text-xs text-violet-500 mt-1">US Dollar to British Pound</p>
            </div>
            @endif

            @if(isset($rates['JPY']))
            <div class="bg-white backdrop-blur-sm rounded-xl shadow-lg p-6 border border-violet-200">
                <div class="flex justify-between items-start mb-2">
                    <h3 class="text-sm font-medium text-violet-600">USD/JPY</h3>
                </div>
                <p class="text-3xl font-bold text-violet-900">{{ number_format($rates['JPY'], 2) }}</p>
                <p class="text-xs text-violet-500 mt-1">US Dollar to Japanese Yen</p>
            </div>
            @endif

            @if(isset($rates['PHP']))
            <div class="bg-white backdrop-blur-sm rounded-xl shadow-lg p-6 border border-violet-200">
                <div class="flex justify-between items-start mb-2">
                    <h3 class="text-sm font-medium text-violet-600">USD/PHP</h3>
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
                    @foreach($rates as $currency => $rate)
                    <div class="flex justify-between items-center py-2 border-b border-violet-50 last:border-0">
                        <div>
                            <p class="font-semibold text-violet-900">USD/{{ $currency }}</p>
                            <p class="text-xs text-violet-500">
                                @switch($currency)
                                    @case('EUR') Euro @break
                                    @case('GBP') British Pound @break
                                    @case('JPY') Japanese Yen @break
                                    @case('PHP') Philippine Peso @break
                                    @case('AUD') Australian Dollar @break
                                    @case('CAD') Canadian Dollar @break
                                    @default {{ $currency }} @break
                                @endswitch
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="font-bold text-violet-900">{{ number_format($rate, 4) }}</p>
                            @php
                                $change = (rand(-200, 200) / 100);
                            @endphp
                            <p class="text-xs {{ $change >= 0 ? 'text-green-600' : 'text-red-600' }}">
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
                <div class="space-y-3">
                    @foreach($gainers as $gainer)
                    <div class="flex justify-between items-center py-3 px-4 bg-green-50 rounded-lg">
                        <div>
                            <p class="font-semibold text-violet-900">{{ $gainer['pair'] }}</p>
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
                <div class="space-y-3">
                    @foreach($losers as $loser)
                    <div class="flex justify-between items-center py-3 px-4 bg-red-50 rounded-lg">
                        <div>
                            <p class="font-semibold text-violet-900">{{ $loser['pair'] }}</p>
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
        <div class="text-center animate-fadeIn">
            <button id="refreshBtn" class="px-6 md:px-8 py-3 md:py-4 bg-gradient-to-r from-violet-300 to-violet-400 text-white text-sm md:text-base font-semibold rounded-lg hover:from-violet-400 hover:to-violet-500 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
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
            labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            datasets: [{
                label: 'Market Trend',
                data: [52, 53, 52.5, 54, 53.8, 55, 54.5],
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
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: false
                }
            }
        }
    });

    // Refresh dashboard
    document.getElementById('refreshBtn').addEventListener('click', async () => {
        try {
            const response = await fetch('{{ route('currency.live-rates') }}');
            const data = await response.json();
            
            if (data) {
                location.reload();
            }
        } catch (error) {
            console.error('Error refreshing dashboard:', error);
        }
    });
</script>
@endpush
