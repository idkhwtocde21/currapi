@extends('layouts.app')

@section('title', 'Multi-Currency Comparison - Currency Analytics')

@section('content')
<div class="max-w-5xl mx-auto animate-fadeInUp">
    <!-- Header -->
    <div class="bg-gradient-to-r from-violet-300 to-violet-400 backdrop-blur-sm rounded-2xl shadow-2xl p-6 md:p-8 mb-4 md:mb-6">
        <h2 class="text-2xl md:text-4xl font-bold text-white text-center mb-2">Multi-Currency Comparison</h2>
        <p class="text-sm md:text-base text-violet-50 text-center">Compare multiple currencies against a base currency</p>
    </div>

    <!-- Form Section -->
    <div class="bg-white backdrop-blur-sm rounded-xl shadow-lg p-6 md:p-8 mb-4 md:mb-6 border border-violet-100">
        <form id="multiCurrencyForm" class="space-y-4 md:space-y-6">
            @csrf
            
            <!-- Base Currency Selection -->
            <div>
                <label class="block text-violet-700 font-medium mb-2">Base Currency:</label>
                <select id="base" 
                        name="base" 
                        class="w-full px-4 py-3 border border-violet-200 bg-white text-violet-900 rounded-lg focus:ring-2 focus:ring-violet-300 focus:border-violet-300 outline-none">
                    @foreach($currencies as $code => $name)
                        <option value="{{ $code }}" {{ $code === 'USD' ? 'selected' : '' }}>
                            {{ $code }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Currency Checkboxes -->
            <div>
                <label class="block text-violet-700 font-medium mb-3">Select Currencies to Compare:</label>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                    @php
                        $defaultCurrencies = ['EUR', 'GBP', 'JPY', 'AUD'];
                    @endphp
                    @foreach($currencies as $code => $name)
                        @if($code !== 'USD')
                            <label class="flex items-center space-x-2 cursor-pointer">
                                <input type="checkbox" 
                                       name="currencies[]" 
                                       value="{{ $code }}" 
                                       class="w-4 h-4 text-violet-600 border-violet-300 rounded focus:ring-violet-500"
                                       {{ in_array($code, $defaultCurrencies) ? 'checked' : '' }}>
                                <span class="text-sm text-violet-700">{{ $code }}</span>
                            </label>
                        @endif
                    @endforeach
                </div>
            </div>

            <!-- Compare Button -->
            <button type="submit" 
                    class="w-full py-3 md:py-4 bg-gradient-to-r from-violet-300 to-violet-400 text-white font-semibold text-base md:text-lg rounded-lg hover:from-violet-400 hover:to-violet-500 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                Compare Currencies
            </button>
        </form>
    </div>

    <!-- Results -->
    <div id="resultsContainer" class="hidden space-y-4 md:space-y-6 animate-fadeInUp">
        <!-- Currency Cards -->
        <div id="currencyCards" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3 md:gap-4 mb-4 md:mb-6"></div>

        <!-- Chart -->
        <div class="bg-white backdrop-blur-sm rounded-xl shadow-lg p-6 md:p-8 border border-violet-100">
            <h3 class="text-xl font-bold text-violet-900 mb-4">Currency Comparison Chart</h3>
            <canvas id="comparisonChart" style="max-height: 300px;"></canvas>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let chart = null;
    const form = document.getElementById('multiCurrencyForm');
    const resultsContainer = document.getElementById('resultsContainer');
    const currencyCards = document.getElementById('currencyCards');

    // Form submission
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const formData = new FormData(form);
        const selectedCurrencies = formData.getAll('currencies[]');
        
        if (selectedCurrencies.length === 0) {
            alert('Please select at least one currency to compare.');
            return;
        }

        const data = {
            base: formData.get('base'),
            currencies: selectedCurrencies
        };

        try {
            const response = await fetch('{{ route('currency.compare') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(data)
            });

            const comparisonData = await response.json();

            if (comparisonData && comparisonData.length > 0) {
                updateCurrencyCards(comparisonData, data.base);
                updateChart(comparisonData, data.base);
                resultsContainer.classList.remove('hidden');
            }
        } catch (error) {
            console.error('Error fetching comparison data:', error);
            alert('An error occurred while fetching data. Please try again.');
        }
    });

    function updateCurrencyCards(data, base) {
        currencyCards.innerHTML = '';
        
        data.forEach(item => {
            const trendColor = item.trend === 'up' ? 'green' : 'red';
            const trendBadge = item.trend === 'up' ? 'Trending Up' : 'Trending Down';
            
            const card = document.createElement('div');
            card.className = 'bg-gradient-to-br from-purple-50 to-white rounded-lg p-4 border border-purple-100';
            card.innerHTML = `
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-lg font-bold text-gray-800">${item.currency}</h3>
                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-${trendColor}-100 text-${trendColor}-600">
                        ${trendBadge}
                    </span>
                </div>
                <p class="text-2xl font-bold text-purple-600 mb-1">${item.rate}</p>
                <p class="text-xs text-gray-500">1 ${base} = ${item.rate} ${item.currency}</p>
                <p class="text-sm mt-2 font-medium ${item.change >= 0 ? 'text-green-600' : 'text-red-600'}">
                    ${item.change >= 0 ? '+' : ''}${item.change.toFixed(2)}%
                </p>
            `;
            
            currencyCards.appendChild(card);
        });
    }

    function updateChart(data, base) {
        const ctx = document.getElementById('comparisonChart').getContext('2d');
        
        if (chart) {
            chart.destroy();
        }

        const colors = [
            'rgba(239, 68, 68, 0.8)',
            'rgba(59, 130, 246, 0.8)',
            'rgba(236, 72, 153, 0.8)',
            'rgba(245, 158, 11, 0.8)',
        ];

        chart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.map(d => d.currency),
                datasets: [{
                    label: `Exchange Rate (Base: ${base})`,
                    data: data.map(d => d.rate),
                    backgroundColor: colors,
                    borderWidth: 0,
                    borderRadius: 8
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
