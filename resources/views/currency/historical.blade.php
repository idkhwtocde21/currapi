@extends('layouts.app')

@section('title', 'Historical Data - Currency Analytics')

@section('content')
<div class="max-w-5xl mx-auto animate-fadeInUp">
    <!-- Header -->
    <div class="bg-gradient-to-r from-violet-300 to-violet-400 backdrop-blur-sm rounded-2xl shadow-2xl p-6 md:p-8 mb-4 md:mb-6">
        <h2 class="text-2xl md:text-4xl font-bold text-white text-center mb-2">Historical Exchange Rates</h2>
        <p class="text-sm md:text-base text-violet-50 text-center">View how exchange rates have moved over time.</p>
    </div>

    <!-- Form Section -->
    <div class="bg-white backdrop-blur-sm rounded-xl shadow-lg p-6 md:p-8 mb-4 md:mb-6 border border-violet-100">
        <form id="historicalForm" class="space-y-4 md:space-y-6">
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
                    <button type="button" class="period-btn active-period px-6 py-2 rounded-lg bg-violet-300 text-white font-medium shadow-md hover:shadow-lg transition-all duration-200 transform hover:-translate-y-0.5" data-days="7">7 Days</button>
                    <button type="button" class="period-btn px-6 py-2 rounded-lg bg-violet-100 text-violet-700 font-medium shadow-sm hover:shadow-md transition-all duration-200 transform hover:-translate-y-0.5" data-days="30">30 Days</button>
                    <button type="button" class="period-btn px-6 py-2 rounded-lg bg-violet-100 text-violet-700 font-medium shadow-sm hover:shadow-md transition-all duration-200 transform hover:-translate-y-0.5" data-days="90">90 Days</button>
                    <button type="button" class="period-btn px-6 py-2 rounded-lg bg-violet-100 text-violet-700 font-medium shadow-sm hover:shadow-md transition-all duration-200 transform hover:-translate-y-0.5" data-days="365">1 Year</button>
                </div>
                <input type="hidden" id="days" name="days" value="7">
            </div>

            <!-- Get Data Button -->
            <button type="submit"
                    id="historicalBtn"
                    class="w-full py-3 md:py-4 bg-gradient-to-r from-violet-300 to-violet-400 text-white font-semibold text-base md:text-lg rounded-lg hover:from-violet-400 hover:to-violet-500 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 disabled:opacity-70 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                <span id="historicalBtnText">Get Historical Data</span>
                <span id="historicalBtnSpinner" class="hidden h-5 w-5 border-2 border-white/40 border-t-white rounded-full animate-spin"></span>
            </button>
        </form>
    </div>

    <!-- Skeleton Loader -->
    <div id="skeletonLoader" class="hidden space-y-4 mb-4">
        <div class="bg-white rounded-xl shadow-lg p-6 border border-violet-100 animate-pulse">
            <div class="h-5 bg-violet-100 rounded w-1/4 mb-4"></div>
            <div class="h-64 bg-violet-50 rounded"></div>
        </div>
        <div class="bg-white rounded-xl shadow-lg border border-violet-100 animate-pulse">
            <div class="h-10 bg-violet-50 rounded-t-xl"></div>
            @for($i = 0; $i < 5; $i++)
            <div class="px-4 py-3 border-b border-violet-50 flex gap-4">
                <div class="h-3 bg-violet-50 rounded w-1/3"></div>
                <div class="h-3 bg-violet-50 rounded w-1/3"></div>
                <div class="h-3 bg-violet-50 rounded w-1/6"></div>
            </div>
            @endfor
        </div>
    </div>

    <!-- Chart -->
    <div id="chartContainer" class="hidden bg-white backdrop-blur-sm rounded-xl shadow-lg p-6 md:p-8 mb-4 md:mb-6 animate-fadeInUp border border-violet-100">
        <h3 class="text-xl font-bold text-violet-900 mb-4">Rate History</h3>
        <canvas id="historicalChart" class="w-full" style="max-height: 400px;"></canvas>
    </div>

    <!-- Data Table -->
    <div id="tableContainer" class="hidden bg-white backdrop-blur-sm rounded-xl shadow-lg overflow-hidden animate-fadeInUp mb-2 border border-violet-100">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-violet-50">
                    <tr>
                        <th class="px-3 py-2 text-left text-xs font-medium text-violet-700 uppercase tracking-wider" id="col1Header">Date</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-violet-700 uppercase tracking-wider" id="col2Header">Rate</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-violet-700 uppercase tracking-wider" id="col3Header">Change</th>
                    </tr>
                </thead>
                <tbody id="tableBody" class="bg-white divide-y divide-gray-200">
                </tbody>
            </table>
        </div>
        <!-- Export Button — only visible after data loads -->
    <div id="exportContainer" class="hidden flex justify-end mt-3 mb-2">
        <button onclick="exportCSV()"
                class="flex items-center gap-2 px-5 py-2 bg-white border border-violet-200 text-violet-700 text-sm font-medium rounded-lg hover:bg-violet-50 hover:border-violet-300 transition-all duration-200 shadow-sm hover:shadow-md">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
            </svg>
            Export CSV
        </button>
    </div>

    <!-- Empty State -->
    <div id="emptyState" class="hidden text-center py-12">
    </div>

    <!-- Empty State -->
    <div id="emptyState" class="hidden text-center py-12">
        <p class="text-violet-400 text-lg font-medium">No data available. Try a different currency pair.</p>
        <p class="text-violet-300 text-sm mt-1">Make sure the API is reachable and try again.</p>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let chart = null;
    let lastExportData = [];
    let lastExportBase = '';
    let lastExportCompare = '';
    const exportContainer = document.getElementById('exportContainer');
    const form = document.getElementById('historicalForm');
    const periodBtns = document.querySelectorAll('.period-btn');
    const daysInput = document.getElementById('days');
    const chartContainer = document.getElementById('chartContainer');
    const tableContainer = document.getElementById('tableContainer');
    const tableBody = document.getElementById('tableBody');
    const skeletonLoader = document.getElementById('skeletonLoader');
    const emptyState = document.getElementById('emptyState');
    const historicalBtn = document.getElementById('historicalBtn');
    const historicalBtnText = document.getElementById('historicalBtnText');
    const historicalBtnSpinner = document.getElementById('historicalBtnSpinner');

    // Period button handlers — consistent class names throughout
    periodBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            periodBtns.forEach(b => {
                b.classList.remove('bg-violet-300', 'text-white', 'active-period');
                b.classList.add('bg-violet-100', 'text-violet-700');
            });
            btn.classList.remove('bg-violet-100', 'text-violet-700');
            btn.classList.add('bg-violet-300', 'text-white', 'active-period');
            daysInput.value = btn.dataset.days;
        });
    });

    function setLoading(isLoading) {
        historicalBtn.disabled = isLoading;
        if (isLoading) {
            historicalBtnText.textContent = 'Loading...';
            historicalBtnSpinner.classList.remove('hidden');
            skeletonLoader.classList.remove('hidden');
            chartContainer.classList.add('hidden');
            tableContainer.classList.add('hidden');
            emptyState.classList.add('hidden');
        } else {
            historicalBtnText.textContent = 'Get Historical Data';
            historicalBtnSpinner.classList.add('hidden');
            skeletonLoader.classList.add('hidden');
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

        function formatRetry(retry) {
            if (!retry) return '';
            const secs = parseInt(retry, 10);
            if (isNaN(secs)) return ` Try again in ${retry} seconds.`;
            if (secs < 60) return ` Try again in ${secs} seconds.`;
            const m = Math.floor(secs / 60);
            const s = secs % 60;
            return s === 0 ? ` Try again in ${m} minute${m>1?'s':''}.` : ` Try again in ${m}m ${s}s.`;
        }

        try {
            const response = await fetch('{{ route('currency.historical.data') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(data)
            });

            const contentType = response.headers.get('content-type') || '';

            if (response.status === 429) {
                let body = {};
                if (contentType.includes('application/json')) {
                    body = await response.json().catch(() => ({}));
                }
                const retry = formatRetry(body.retry_after);
                Swal.fire({ icon: 'warning', title: 'Too Many Requests', text: (body.message || 'Rate limit exceeded.') + retry, confirmButtonColor: '#374151' });
                setLoading(false);
                return;
            }

            let historicalData;
            if (contentType.includes('application/json')) {
                historicalData = await response.json();
            } else {
                const text = await response.text();
                throw new Error('Unexpected server response while fetching historical data.');
            }

            if (historicalData && historicalData.length > 0) {
                updateChart(historicalData, data.base, data.compare);
                updateTable(historicalData, data.days);
                chartContainer.classList.remove('hidden');
                tableContainer.classList.remove('hidden');
                exportContainer.classList.remove('hidden');
                emptyState.classList.add('hidden');
                lastExportData = historicalData;
                lastExportBase = data.base;
                lastExportCompare = data.compare;
            } else {
                chartContainer.classList.add('hidden');
                tableContainer.classList.add('hidden');
                exportContainer.classList.add('hidden');
                emptyState.classList.remove('hidden');
            }
        } catch (error) {
            console.error('Error fetching historical data:', error);
            chartContainer.classList.add('hidden');
            tableContainer.classList.add('hidden');
            emptyState.classList.add('hidden');
            Swal.fire({
                icon: 'error',
                title: 'Failed to Load Data',
                text: error.message || 'An error occurred while fetching historical data. Please try again.',
                confirmButtonColor: '#374151',
                timer: 4000
            });
        } finally {
            setLoading(false);
        }
    });

    function updateChart(data, base, compare) {
        const ctx = document.getElementById('historicalChart').getContext('2d');

        if (chart) {
            chart.destroy();
        }

        chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.map(d => {
                    const date = new Date(d.date);
                    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
                }),
                datasets: [{
                    label: `${base} to ${compare}`,
                    data: data.map(d => parseFloat(d.rate)),
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
                    y: {
                        beginAtZero: false,
                        ticks: {
                            callback: function(value) {
                                return value.toFixed(4);
                            }
                        }
                    }
                }
            }
        });
    }

    function updateTable(data, days) {
        tableBody.innerHTML = '';

        const useMonthly = days >= 90;

        document.getElementById('col1Header').textContent = useMonthly ? 'Month' : 'Date';
        document.getElementById('col2Header').textContent = useMonthly ? 'Avg Rate' : 'Rate';
        document.getElementById('col3Header').textContent = useMonthly ? 'Avg Change' : 'Change';

        if (useMonthly) {
            const monthlyData = {};
            data.forEach(item => {
                const date = new Date(item.date);
                const monthKey = `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}`;

                if (!monthlyData[monthKey]) {
                    monthlyData[monthKey] = {
                        rates: [],
                        changes: [],
                        month: date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' })
                    };
                }

                monthlyData[monthKey].rates.push(parseFloat(item.rate));
                monthlyData[monthKey].changes.push(parseFloat(item.change));
            });

            Object.keys(monthlyData).sort().forEach(monthKey => {
                const monthData = monthlyData[monthKey];
                const avgRate = monthData.rates.reduce((a, b) => a + b, 0) / monthData.rates.length;
                const avgChange = monthData.changes.reduce((a, b) => a + b, 0) / monthData.changes.length;

                const row = document.createElement('tr');
                row.className = 'hover:bg-gray-50';

                const changeClass = avgChange >= 0 ? 'text-green-600' : 'text-red-600';
                const changeSign = avgChange >= 0 ? '+' : '';

                row.innerHTML = `
                    <td class="px-3 py-2 whitespace-nowrap text-xs text-violet-900 font-medium">${monthData.month}</td>
                    <td class="px-3 py-2 whitespace-nowrap text-xs text-violet-900 font-medium">${avgRate.toFixed(4)}</td>
                    <td class="px-3 py-2 whitespace-nowrap text-xs font-semibold ${changeClass}">${changeSign}${avgChange.toFixed(4)}%</td>
                `;

                tableBody.appendChild(row);
            });
        } else {
            data.slice().reverse().forEach(item => {
                const row = document.createElement('tr');
                row.className = 'hover:bg-gray-50';

                const changeClass = item.change >= 0 ? 'text-green-600' : 'text-red-600';
                const changeSign = item.change >= 0 ? '+' : '';

                const date = new Date(item.date);
                const formattedDate = date.toLocaleDateString('en-US', {
                    month: 'numeric',
                    day: 'numeric',
                    year: '2-digit'
                });

                row.innerHTML = `
                    <td class="px-3 py-2 whitespace-nowrap text-xs text-violet-900">${formattedDate}</td>
                    <td class="px-3 py-2 whitespace-nowrap text-xs text-violet-900 font-medium">${parseFloat(item.rate).toFixed(4)}</td>
                    <td class="px-3 py-2 whitespace-nowrap text-xs font-semibold ${changeClass}">${changeSign}${parseFloat(item.change).toFixed(4)}%</td>
                `;

                tableBody.appendChild(row);
            });
        }
    }

    function exportCSV() {
        if (!lastExportData.length) return;

        const days = parseInt(daysInput.value);
        const useMonthly = days >= 90;

        let csvRows = [];

        if (useMonthly) {
            csvRows.push(['Month', 'Avg Rate', 'Avg Change (%)']);
            const monthlyData = {};
            lastExportData.forEach(item => {
                const date = new Date(item.date);
                const monthKey = `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}`;
                if (!monthlyData[monthKey]) {
                    monthlyData[monthKey] = {
                        rates: [],
                        changes: [],
                        month: date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' })
                    };
                }
                monthlyData[monthKey].rates.push(parseFloat(item.rate));
                monthlyData[monthKey].changes.push(parseFloat(item.change));
            });

            Object.keys(monthlyData).sort().forEach(key => {
                const m = monthlyData[key];
                const avgRate = (m.rates.reduce((a, b) => a + b, 0) / m.rates.length).toFixed(4);
                const avgChange = (m.changes.reduce((a, b) => a + b, 0) / m.changes.length).toFixed(4);
                csvRows.push([m.month, avgRate, avgChange]);
            });
        } else {
            csvRows.push(['Date', 'Rate', 'Change (%)']);
            lastExportData.slice().reverse().forEach(item => {
                csvRows.push([item.date, parseFloat(item.rate).toFixed(4), parseFloat(item.change).toFixed(4)]);
            });
        }

        const csvContent = csvRows.map(row => row.join(',')).join('\n');
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);

        const link = document.createElement('a');
        link.href = url;
        link.download = `${lastExportBase}_${lastExportCompare}_${days}days.csv`;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        URL.revokeObjectURL(url);
    }
</script>
@endpush