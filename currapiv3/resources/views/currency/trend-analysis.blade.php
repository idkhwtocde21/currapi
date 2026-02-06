@extends('layouts.app')

@section('title', 'Trend Analysis - Currency Analytics')

@section('content')
<div class="max-w-5xl mx-auto animate-fadeInUp">
    <!-- Header -->
    <div class="bg-gradient-to-r from-violet-300 to-violet-400 backdrop-blur-sm rounded-2xl shadow-2xl p-6 md:p-8 mb-4 md:mb-6">
        <h2 class="text-2xl md:text-4xl font-bold text-white text-center mb-2">Trend Analysis</h2>
        <p class="text-sm md:text-base text-violet-50 text-center"></p>
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
                    <button type="button" class="period-btn px-6 py-2 rounded-lg bg-violet-300 text-white font-medium shadow-md hover:shadow-lg transition-all duration-200 transform hover:-translate-y-0.5" data-days="7">7 Days</button>
                    <button type="button" class="period-btn px-6 py-2 rounded-lg bg-violet-100 text-violet-700 font-medium shadow-sm hover:shadow-md transition-all duration-200 transform hover:-translate-y-0.5" data-days="30">30 Days</button>
                    <button type="button" class="period-btn px-6 py-2 rounded-lg bg-violet-100 text-violet-700 font-medium shadow-sm hover:shadow-md transition-all duration-200 transform hover:-translate-y-0.5" data-days="90">90 Days</button>
                    <button type="button" class="period-btn px-6 py-2 rounded-lg bg-violet-100 text-violet-700 font-medium shadow-sm hover:shadow-md transition-all duration-200 transform hover:-translate-y-0.5" data-days="365">1 Year</button>
                </div>
                <input type="hidden" id="days" name="days" value="7">
            </div>

            <!-- Analyze Button -->
            <button type="submit" 
                    class="w-full py-3 md:py-4 bg-gradient-to-r from-violet-300 to-violet-400 text-white font-semibold text-base md:text-lg rounded-lg hover:from-violet-400 hover:to-violet-500 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                Analyze Trend
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

    <!-- Chart -->
    <div id="chartContainer" class="hidden bg-white backdrop-blur-sm rounded-xl shadow-lg p-6 md:p-8 animate-fadeInUp border border-violet-100">
        <h3 class="text-xl font-bold text-violet-900 mb-4">Trend Analysis Chart</h3>
        <canvas id="trendChart" class="w-full" style="max-height: 400px;"></canvas>
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

    // Period button handlers
    periodBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            periodBtns.forEach(b => {
                b.classList.remove('bg-purple-600', 'text-white');
                b.classList.add('bg-purple-100', 'text-purple-600');
            });
            btn.classList.remove('bg-purple-100', 'text-purple-600');
            btn.classList.add('bg-purple-600', 'text-white');
            daysInput.value = btn.dataset.days;
        });
    });

    // Form submission
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const formData = new FormData(form);
        const data = {
            base: formData.get('base'),
            compare: formData.get('compare'),
            days: parseInt(formData.get('days'))
        };

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
                updateChart(trendData.data, data.base, data.compare);
                statsContainer.classList.remove('hidden');
                chartContainer.classList.remove('hidden');
            }
        } catch (error) {
            console.error('Error fetching trend data:', error);
            alert('An error occurred while fetching data. Please try again.');
        }
    });

    function updateStats(data) {
        document.getElementById('currentRate').textContent = data.current;
        document.getElementById('averageRate').textContent = data.average;
        document.getElementById('highestRate').textContent = data.highest;
        document.getElementById('lowestRate').textContent = data.lowest;
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
                        labels: {
                            font: {
                                size: 12
                            }
                        }
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
