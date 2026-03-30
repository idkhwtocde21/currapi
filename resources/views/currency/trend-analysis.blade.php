@extends('layouts.app')

@section('title', 'Trend Analysis - Currency Analytics')

@section('content')
<div class="max-w-5xl mx-auto animate-fadeInUp">
    <!-- Header -->
    <div class="bg-gradient-to-r from-violet-300 to-violet-400 backdrop-blur-sm rounded-2xl shadow-2xl p-6 md:p-8 mb-4 md:mb-6">
        <h2 class="text-2xl md:text-4xl font-bold text-white text-center mb-2">Trend Analysis</h2>
        <p class="text-sm md:text-base text-violet-50 text-center">Analyze currency movement patterns over selected time periods.</p>
    </div>

    <!-- Form Section -->
    <div class="bg-white backdrop-blur-sm rounded-xl shadow-lg p-6 md:p-8 mb-4 md:mb-6 border border-violet-100">
        <form id="trendForm" class="space-y-4 md:space-y-6">
            @csrf

            <!-- Currency Selection -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-violet-700 font-medium mb-2">Base Currency:</label>
                    <select id="base"
                            name="base"
                            class="w-full px-4 py-3 border border-violet-200 bg-white text-violet-900 rounded-lg focus:ring-2 focus:ring-violet-300 focus:border-violet-300 outline-none">
                        @foreach($currencies as $code => $name)
                            <option value="{{ $code }}" {{ $code === 'USD' ? 'selected' : '' }}>
                                {{ $code }} - {{ $name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-violet-700 font-medium mb-2">Compare Currency:</label>
                    <select id="compare"
                            name="compare"
                            class="w-full px-4 py-3 border border-violet-200 bg-white text-violet-900 rounded-lg focus:ring-2 focus:ring-violet-300 focus:border-violet-300 outline-none">
                        @foreach($currencies as $code => $name)
                            <option value="{{ $code }}" {{ $code === 'PHP' ? 'selected' : '' }}>
                                {{ $code }} - {{ $name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Time Period -->
            <div>
                <label class="block text-violet-700 font-medium mb-3">Time Period:</label>
                <div class="flex flex-wrap gap-3">
                    <!-- All buttons use violet- classes consistently — no purple- mismatch -->
                    <button type="button" class="period-btn px-6 py-2 rounded-lg bg-violet-300 text-white font-medium shadow-md hover:shadow-lg transition-all duration-200 transform hover:-translate-y-0.5" data-days="7">7 Days</button>
                    <button type="button" class="period-btn px-6 py-2 rounded-lg bg-violet-100 text-violet-700 font-medium shadow-sm hover:shadow-md transition-all duration-200 transform hover:-translate-y-0.5" data-days="30">30 Days</button>
                    <button type="button" class="period-btn px-6 py-2 rounded-lg bg-violet-100 text-violet-700 font-medium shadow-sm hover:shadow-md transition-all duration-200 transform hover:-translate-y-0.5" data-days="90">90 Days</button>
                    <button type="button" class="period-btn px-6 py-2 rounded-lg bg-violet-100 text-violet-700 font-medium shadow-sm hover:shadow-md transition-all duration-200 transform hover:-translate-y-0.5" data-days="365">1 Year</button>
                </div>
                <input type="hidden" id="days" name="days" value="7">
            </div>

            <!-- Analyze Button -->
            <button type="submit"
                    id="trendBtn"
                    class="w-full py-3 md:py-4 bg-gradient-to-r from-violet-300 to-violet-400 text-white font-semibold text-base md:text-lg rounded-lg hover:from-violet-400 hover:to-violet-500 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 disabled:opacity-70 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                <span id="trendBtnText">Analyze Trend</span>
                <span id="trendBtnSpinner" class="hidden h-5 w-5 border-2 border-white/40 border-t-white rounded-full animate-spin"></span>
            </button>
        </form>
    </div>

    <!-- Statistics Cards -->
    <div id="statsContainer" class="hidden grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-4 mb-4 md:mb-6 animate-fadeInUp">
        <div class="bg-white border border-violet-100 rounded-lg p-4 shadow">
            <p class="text-xs text-violet-600 mb-1">Current Rate</p>
            <p class="text-2xl font-bold text-green-600" id="currentRate">-</p>
        </div>
        <div class="bg-white border border-violet-100 rounded-lg p-4 shadow">
            <p class="text-xs text-violet-600 mb-1">Average Rate</p>
            <p class="text-2xl font-bold text-violet-600" id="averageRate">-</p>
        </div>
        <div class="bg-white border border-violet-100 rounded-lg p-4 shadow">
            <p class="text-xs text-violet-600 mb-1">Highest</p>
            <p class="text-2xl font-bold text-pink-600" id="highestRate">-</p>
        </div>
        <div class="bg-white border border-violet-100 rounded-lg p-4 shadow">
            <p class="text-xs text-violet-600 mb-1">Lowest</p>
            <p class="text-2xl font-bold text-orange-600" id="lowestRate">-</p>
        </div>
    </div>

    <!-- Period Change Banner -->
    <div id="periodChangeBanner" class="hidden mb-4 px-5 py-3 rounded-xl border text-sm font-medium animate-fadeInUp flex items-center gap-2">
        <span id="periodChangeIcon"></span>
        <span id="periodChangeText"></span>
    </div>

    <!-- Chart -->
    <div id="chartContainer" class="hidden bg-white backdrop-blur-sm rounded-xl shadow-lg p-6 md:p-8 animate-fadeInUp border border-violet-100">
        <h3 class="text-xl font-bold text-violet-900 mb-4">Trend Analysis Chart</h3>
        <canvas id="trendChart" class="w-full" style="max-height: 400px;"></canvas>
    </div>

    <!-- Empty State -->
    <div id="emptyState" class="hidden text-center py-12">
        <p class="text-violet-400 text-lg font-medium">No trend data available for this pair.</p>
        <p class="text-violet-300 text-sm mt-1">Try a different currency combination.</p>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let chart = null;
    const form = document.getElementById('trendForm');
    const periodBtns = document.querySelectorAll('.period-btn');
    const daysInput = document.getElementById('days');
    const statsContainer = document.getElementById('statsContainer');
    const chartContainer = document.getElementById('chartContainer');
    const emptyState = document.getElementById('emptyState');
    const trendBtn = document.getElementById('trendBtn');
    const trendBtnText = document.getElementById('trendBtnText');
    const trendBtnSpinner = document.getElementById('trendBtnSpinner');
    const periodChangeBanner = document.getElementById('periodChangeBanner');

    // Period button handlers — all violet- classes, no purple- mismatch
    periodBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            periodBtns.forEach(b => {
                b.classList.remove('bg-violet-300', 'text-white');
                b.classList.add('bg-violet-100', 'text-violet-700');
            });
            btn.classList.remove('bg-violet-100', 'text-violet-700');
            btn.classList.add('bg-violet-300', 'text-white');
            daysInput.value = btn.dataset.days;
        });
    });

    function setLoading(isLoading) {
        trendBtn.disabled = isLoading;
        if (isLoading) {
            trendBtnText.textContent = 'Analyzing...';
            trendBtnSpinner.classList.remove('hidden');
        } else {
            trendBtnText.textContent = 'Analyze Trend';
            trendBtnSpinner.classList.add('hidden');
        }
    }

    // Form submission
    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        const formData = new FormData(form);
        const data = {
            base: formData.get('base'),
            compare: formData.get('compare'),
            days: parseInt(formData.get('days'))
        };

        setLoading(true);

        try {
            const response = await fetch('{{ route('currency.trend.data') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(data)
            });

            const trendData = await response.json();

            if (trendData) {
                updateStats(trendData);
                updatePeriodBanner(trendData, data.base, data.compare, data.days);
                updateChart(trendData.data, data.base, data.compare);
                statsContainer.classList.remove('hidden');
                chartContainer.classList.remove('hidden');
                periodChangeBanner.classList.remove('hidden');
                emptyState.classList.add('hidden');
            } else {
                statsContainer.classList.add('hidden');
                chartContainer.classList.add('hidden');
                periodChangeBanner.classList.add('hidden');
                emptyState.classList.remove('hidden');
            }
        } catch (error) {
            console.error('Error fetching trend data:', error);
            Swal.fire({
                icon: 'error',
                title: 'Failed to Analyze',
                text: 'An error occurred while fetching trend data. Please try again.',
                confirmButtonColor: '#374151',
                timer: 4000
            });
        } finally {
            setLoading(false);
        }
    });

    function updateStats(data) {
        document.getElementById('currentRate').textContent = data.current;
        document.getElementById('averageRate').textContent = data.average;
        document.getElementById('highestRate').textContent = data.highest;
        document.getElementById('lowestRate').textContent = data.lowest;
    }

    function updatePeriodBanner(data, base, compare, days) {
        const change = data.period_change ?? 0;
        const isUp = change >= 0;
        const sign = isUp ? '+' : '';
        const label = days >= 365 ? '1 Year' : days >= 90 ? '90 Days' : days >= 30 ? '30 Days' : '7 Days';

        periodChangeBanner.className = `mb-4 px-5 py-3 rounded-xl border text-sm font-medium animate-fadeInUp flex items-center gap-2 ${
            isUp
                ? 'bg-green-50 border-green-200 text-green-700'
                : 'bg-red-50 border-red-200 text-red-700'
        }`;

        document.getElementById('periodChangeIcon').textContent = isUp ? '▲' : '▼';
        document.getElementById('periodChangeText').textContent =
            `${base}/${compare} moved ${sign}${change}% over the last ${label}.`;
    }

    function updateChart(data, base, compare) {
        const ctx = document.getElementById('trendChart').getContext('2d');

        if (chart) {
            chart.destroy();
        }

        chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.map(d => d.date),
                datasets: [{
                    label: `${base} to ${compare}`,
                    data: data.map(d => d.rate),
                    borderColor: 'rgb(147, 51, 234)',
                    backgroundColor: 'rgba(147, 51, 234, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointRadius: 4,
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
                        labels: { font: { size: 12 } }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: false,
                        ticks: {
                            callback: function(value) {
                                return value.toFixed(2);
                            }
                        }
                    }
                }
            }
        });
    }
</script>
@endpush