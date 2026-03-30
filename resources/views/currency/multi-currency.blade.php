@extends('layouts.app')

@section('title', 'Multi-Currency Comparison - Currency Analytics')

@section('content')
<div class="max-w-5xl mx-auto animate-fadeInUp">
    <!-- Header -->
    <div class="bg-gradient-to-r from-violet-300 to-violet-400 backdrop-blur-sm rounded-2xl shadow-2xl p-6 md:p-8 mb-4 md:mb-6">
        <h2 class="text-2xl md:text-4xl font-bold text-white text-center mb-2">Multi-Currency Comparison</h2>
        <p class="text-sm md:text-base text-violet-50 text-center">Compare multiple currencies against a single base at a glance.</p>
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
                <div class="flex items-center justify-between mb-3">
                    <label class="block text-violet-700 font-medium">Select Currencies to Compare:</label>
                    <button type="button"
                            id="resetSelectionsBtn"
                            class="text-xs text-violet-400 hover:text-violet-600 underline transition-colors duration-200">
                        Reset to defaults
                    </button>
                </div>
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
                                       class="currency-checkbox w-4 h-4 text-violet-600 border-violet-300 rounded focus:ring-violet-500"
                                       {{ in_array($code, $defaultCurrencies) ? 'checked' : '' }}>
                                <span class="text-sm text-violet-700">
                                    {{ $code }}
                                </span>
                            </label>
                        @endif
                    @endforeach
                </div>
            </div>

            <!-- Compare Button -->
            <button type="submit"
                    id="compareBtn"
                    class="w-full py-3 md:py-4 bg-gradient-to-r from-violet-300 to-violet-400 text-white font-semibold text-base md:text-lg rounded-lg hover:from-violet-400 hover:to-violet-500 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 disabled:opacity-70 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                <span id="compareBtnText">Compare Currencies</span>
                <span id="compareBtnSpinner" class="hidden h-5 w-5 border-2 border-white/40 border-t-white rounded-full animate-spin"></span>
            </button>
        </form>
    </div>

    <!-- Results -->
    <div id="resultsContainer" class="hidden space-y-4 md:space-y-6 animate-fadeInUp">
        <!-- Currency Cards -->
        <div id="currencyCards" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3 md:gap-4 mb-4 md:mb-6"></div>

        <!-- Chart -->
        <div class="bg-white backdrop-blur-sm rounded-xl shadow-lg p-6 md:p-8 border border-violet-100">
            <h3 class="text-xl font-bold text-violet-900 mb-1">Currency Comparison Chart</h3>
            <p class="text-xs text-violet-400 mb-4">
                Showing exchange rate per 1 <span id="chartBaseLabel">USD</span>.
                Rates are displayed on their actual values — hover bars for exact amounts.
            </p>
            <canvas id="comparisonChart" style="max-height: 320px;"></canvas>
        </div>
    </div>

    <!-- Empty State -->
    <div id="emptyState" class="hidden text-center py-12">
        <p class="text-violet-400 text-lg font-medium">No results to display.</p>
        <p class="text-violet-300 text-sm mt-1">Select at least one currency and try again.</p>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let chart = null;

    const STORAGE_KEY_CURRENCIES = 'multicurrency_selected';
    const STORAGE_KEY_BASE       = 'multicurrency_base';
    const DEFAULT_CURRENCIES     = ['EUR', 'GBP', 'JPY', 'AUD'];

    const form            = document.getElementById('multiCurrencyForm');
    const resultsContainer = document.getElementById('resultsContainer');
    const currencyCards   = document.getElementById('currencyCards');
    const emptyState      = document.getElementById('emptyState');
    const compareBtn      = document.getElementById('compareBtn');
    const compareBtnText  = document.getElementById('compareBtnText');
    const compareBtnSpinner = document.getElementById('compareBtnSpinner');
    const baseSelect      = document.getElementById('base');
    const checkboxes      = document.querySelectorAll('.currency-checkbox');
    const resetBtn        = document.getElementById('resetSelectionsBtn');

    // ── Restore saved selections from localStorage on page load ──
    function restoreSelections() {
        const savedBase = localStorage.getItem(STORAGE_KEY_BASE);
        const savedCurrencies = localStorage.getItem(STORAGE_KEY_CURRENCIES);

        if (savedBase) {
            baseSelect.value = savedBase;
        }

        if (savedCurrencies) {
            const selected = JSON.parse(savedCurrencies);
            checkboxes.forEach(cb => {
                cb.checked = selected.includes(cb.value);
            });
        }
        // If nothing saved, the Blade-rendered defaults (EUR, GBP, JPY, AUD) stay checked
    }

    // ── Save current selections to localStorage ──
    function saveSelections() {
        const selected = Array.from(checkboxes)
            .filter(cb => cb.checked)
            .map(cb => cb.value);

        localStorage.setItem(STORAGE_KEY_CURRENCIES, JSON.stringify(selected));
        localStorage.setItem(STORAGE_KEY_BASE, baseSelect.value);
    }

    // ── Reset to defaults ──
    resetBtn.addEventListener('click', () => {
        checkboxes.forEach(cb => {
            cb.checked = DEFAULT_CURRENCIES.includes(cb.value);
        });
        baseSelect.value = 'USD';
        localStorage.removeItem(STORAGE_KEY_CURRENCIES);
        localStorage.removeItem(STORAGE_KEY_BASE);
    });

    // Save on any change
    checkboxes.forEach(cb => cb.addEventListener('change', saveSelections));
    baseSelect.addEventListener('change', saveSelections);

    // Restore on load
    restoreSelections();

    // ── Loading state ──
    function setLoading(isLoading) {
        compareBtn.disabled = isLoading;
        if (isLoading) {
            compareBtnText.textContent = 'Comparing...';
            compareBtnSpinner.classList.remove('hidden');
        } else {
            compareBtnText.textContent = 'Compare Currencies';
            compareBtnSpinner.classList.add('hidden');
        }
    }

    // ── Form submission ──
    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        const formData = new FormData(form);
        const selectedCurrencies = formData.getAll('currencies[]');

        if (selectedCurrencies.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'No Currencies Selected',
                text: 'Please select at least one currency to compare.',
                confirmButtonColor: '#374151',
                timer: 3000
            });
            return;
        }

        // Save before submitting
        saveSelections();

        const data = {
            base:       formData.get('base'),
            currencies: selectedCurrencies
        };

        setLoading(true);

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
                document.getElementById('chartBaseLabel').textContent = data.base;
                resultsContainer.classList.remove('hidden');
                emptyState.classList.add('hidden');
            } else {
                resultsContainer.classList.add('hidden');
                emptyState.classList.remove('hidden');
            }
        } catch (error) {
            console.error('Error fetching comparison data:', error);
            resultsContainer.classList.add('hidden');
            emptyState.classList.add('hidden');
            Swal.fire({
                icon: 'error',
                title: 'Comparison Failed',
                text: 'An error occurred while fetching comparison data. Please try again.',
                confirmButtonColor: '#374151',
                timer: 4000
            });
        } finally {
            setLoading(false);
        }
    });

    // ── Currency cards ──
    function updateCurrencyCards(data, base) {
        currencyCards.innerHTML = '';

        data.forEach(item => {
            const isUp      = item.trend === 'up';
            const trendColor = isUp ? 'green' : 'red';
            const trendIcon  = isUp ? '▲' : '▼';
            const trendLabel = isUp ? 'Trending Up' : 'Trending Down';

            const card = document.createElement('div');
            card.className = 'currency-result-card rounded-lg p-4 border border-violet-100 shadow';
            card.innerHTML = `
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-lg font-bold text-violet-900">
                        ${currencyFlag(item.currency)} ${item.currency}
                    </h3>
                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-${trendColor}-100 text-${trendColor}-600">
                        ${trendIcon} ${trendLabel}
                    </span>
                </div>
                <p class="text-2xl font-bold text-violet-700 mb-1">${item.rate}</p>
                <p class="text-xs text-violet-400">1 ${base} = ${item.rate} ${item.currency}</p>
                <p class="text-sm mt-2 font-medium ${item.change >= 0 ? 'text-green-600' : 'text-red-600'}">
                    ${item.change >= 0 ? '+' : ''}${item.change.toFixed(2)}% today
                </p>
            `;

            currencyCards.appendChild(card);
        });
    }

    // ── Chart — clean grouped bar chart, no log scale ──
    function updateChart(data, base) {
        const ctx = document.getElementById('comparisonChart').getContext('2d');

        if (chart) {
            chart.destroy();
        }

        const colors = [
            'rgba(139, 92, 246, 0.75)',
            'rgba(59, 130, 246, 0.75)',
            'rgba(236, 72, 153, 0.75)',
            'rgba(245, 158, 11, 0.75)',
            'rgba(16, 185, 129, 0.75)',
            'rgba(239, 68, 68, 0.75)',
            'rgba(14, 165, 233, 0.75)',
            'rgba(168, 85, 247, 0.75)',
            'rgba(251, 146, 60, 0.75)',
            'rgba(52, 211, 153, 0.75)',
        ];

        const borderColors = colors.map(c => c.replace('0.75', '1'));

        // Split into two groups: "normal" rates (< 10) and "large" rates (>= 10)
        // This way JPY doesn't crush EUR on the same axis
        const normalData = data.filter(d => d.rate < 10);
        const largeData  = data.filter(d => d.rate >= 10);

        // If everything fits in one group, just use a single chart
        const hasLarge  = largeData.length > 0;
        const hasNormal = normalData.length > 0;
        const mixed     = hasLarge && hasNormal;

        if (mixed) {
            // Render two separate mini charts stacked
            document.getElementById('comparisonChart').style.display = 'none';

            // Remove old split charts if they exist
            ['splitChart1', 'splitChart2', 'splitLabel1', 'splitLabel2'].forEach(id => {
                const el = document.getElementById(id);
                if (el) el.remove();
            });

            const container = document.getElementById('comparisonChart').parentNode;

            // Label + canvas for normal rates
            const label1 = document.createElement('p');
            label1.id = 'splitLabel1';
            label1.className = 'text-xs font-medium text-violet-500 mb-1 mt-2';
            label1.textContent = `Rates under 10 (per 1 ${base})`;
            container.appendChild(label1);

            const canvas1 = document.createElement('canvas');
            canvas1.id = 'splitChart1';
            canvas1.style.maxHeight = '200px';
            container.appendChild(canvas1);

            // Label + canvas for large rates
            const label2 = document.createElement('p');
            label2.id = 'splitLabel2';
            label2.className = 'text-xs font-medium text-violet-500 mb-1 mt-4';
            label2.textContent = `Rates 10 and above (per 1 ${base})`;
            container.appendChild(label2);

            const canvas2 = document.createElement('canvas');
            canvas2.id = 'splitChart2';
            canvas2.style.maxHeight = '200px';
            container.appendChild(canvas2);

            renderBarChart(canvas1.getContext('2d'), normalData, base, colors, borderColors);
            renderBarChart(canvas2.getContext('2d'), largeData, base, colors.slice(4), borderColors.slice(4));

        } else {
            // Everything on one chart — remove split charts if they exist
            ['splitChart1', 'splitChart2', 'splitLabel1', 'splitLabel2'].forEach(id => {
                const el = document.getElementById(id);
                if (el) el.remove();
            });

            document.getElementById('comparisonChart').style.display = 'block';
            chart = renderBarChart(ctx, data, base, colors, borderColors);
        }
    }

    function renderBarChart(ctx, data, base, colors, borderColors) {
        return new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.map(d => `${currencyFlag(d.currency)} ${d.currency}`),
                datasets: [{
                    label: `Rate per 1 ${base}`,
                    data: data.map(d => d.rate),
                    backgroundColor: colors.slice(0, data.length),
                    borderColor: borderColors.slice(0, data.length),
                    borderWidth: 1.5,
                    borderRadius: 8,
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            title: function(context) {
                                return context[0].label.replace(/\s+/g, ' ').trim();
                            },
                            label: function(context) {
                                const item = data[context.dataIndex];
                                return [
                                    ` Rate: ${context.parsed.y.toFixed(4)}`,
                                    ` 1 ${base} = ${item.rate} ${item.currency}`,
                                    ` Change: ${item.change >= 0 ? '+' : ''}${item.change.toFixed(2)}% today`
                                ];
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: {
                            font: { size: 12 },
                            maxRotation: 0
                        }
                    },
                    y: {
                        beginAtZero: false,
                        grid: {
                            color: 'rgba(139, 92, 246, 0.08)'
                        },
                        ticks: {
                            callback: function(value) {
                                if (value >= 1000) return value.toLocaleString();
                                if (value >= 10)   return value.toFixed(2);
                                return value.toFixed(4);
                            }
                        }
                    }
                }
            }
        });
    }
</script>
@endpush