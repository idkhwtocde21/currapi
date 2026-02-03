@extends('layouts.app')

@section('title', 'Historical Data - Currency Analytics')

@section('content')
<div class="max-w-5xl mx-auto animate-fadeInUp">
    <!-- Header -->
    <div class="bg-gradient-to-r from-violet-300 to-violet-400 backdrop-blur-sm rounded-2xl shadow-2xl p-6 md:p-8 mb-4 md:mb-6">
        <h2 class="text-2xl md:text-4xl font-bold text-white text-center mb-2">Historical Exchange Rates</h2>
        <p class="text-sm md:text-base text-violet-50 text-center">View historical exchange rate data</p>
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
                    <button type="button" class="period-btn px-6 py-2 rounded-lg bg-violet-300 text-white font-medium shadow-md hover:shadow-lg transition-all duration-200 transform hover:-translate-y-0.5" data-days="7">7 Days</button>
                    <button type="button" class="period-btn px-6 py-2 rounded-lg bg-violet-100 text-violet-700 font-medium shadow-sm hover:shadow-md transition-all duration-200 transform hover:-translate-y-0.5" data-days="30">30 Days</button>
                    <button type="button" class="period-btn px-6 py-2 rounded-lg bg-violet-100 text-violet-700 font-medium shadow-sm hover:shadow-md transition-all duration-200 transform hover:-translate-y-0.5" data-days="90">90 Days</button>
                    <button type="button" class="period-btn px-6 py-2 rounded-lg bg-violet-100 text-violet-700 font-medium shadow-sm hover:shadow-md transition-all duration-200 transform hover:-translate-y-0.5" data-days="365">1 Year</button>
                </div>
                <input type="hidden" id="days" name="days" value="7">
            </div>

            <!-- Get Data Button -->
            <button type="submit" 
                    class="w-full py-3 md:py-4 bg-gradient-to-r from-violet-300 to-violet-400 text-white font-semibold text-base md:text-lg rounded-lg hover:from-violet-400 hover:to-violet-500 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                Get Historical Data
            </button>
        </form>
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
    </div>
</div>
@endsection

@push('scripts')
<script>
    let chart = null;
    const form = document.getElementById('historicalForm');
    const periodBtns = document.querySelectorAll('.period-btn');
    const daysInput = document.getElementById('days');
    const chartContainer = document.getElementById('chartContainer');
    const tableContainer = document.getElementById('tableContainer');
    const tableBody = document.getElementById('tableBody');

    // Period button handlers
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
            const response = await fetch('{{ route('currency.historical.data') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(data)
            });

            const historicalData = await response.json();

            if (historicalData && historicalData.length > 0) {
                updateChart(historicalData, data.base, data.compare);
                updateTable(historicalData, data.days);
                chartContainer.classList.remove('hidden');
                tableContainer.classList.remove('hidden');
            }
        } catch (error) {
            console.error('Error fetching historical data:', error);
            alert('An error occurred while fetching data. Please try again.');
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
                    legend: {
                        display: true,
                        position: 'top'
                    }
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
        
        const useMonthly = days >= 90; // Use monthly for 90 days and 1 year
        
        // Update table headers
        document.getElementById('col1Header').textContent = useMonthly ? 'Month' : 'Date';
        document.getElementById('col2Header').textContent = useMonthly ? 'Avg Rate' : 'Rate';
        document.getElementById('col3Header').textContent = useMonthly ? 'Avg Change' : 'Change';
        
        if (useMonthly) {
            // Group data by month
            const monthlyData = {};
            data.forEach(item => {
                const date = new Date(item.date);
                const monthKey = `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}`;
                
                if (!monthlyData[monthKey]) {
                    monthlyData[monthKey] = {
                        dates: [],
                        rates: [],
                        changes: [],
                        month: date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' })
                    };
                }
                
                monthlyData[monthKey].dates.push(date);
                monthlyData[monthKey].rates.push(parseFloat(item.rate));
                monthlyData[monthKey].changes.push(parseFloat(item.change));
            });
            
            // Display monthly aggregated data (oldest to newest)
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
                    <td class="px-3 py-2 whitespace-nowrap text-xs font-semibold ${changeClass}">${changeSign}${avgChange.toFixed(2)}%</td>
                `;
                
                tableBody.appendChild(row);
            });
        } else {
            // Display daily data for 7 and 30 days
            data.slice().reverse().forEach(item => {
                const row = document.createElement('tr');
                row.className = 'hover:bg-gray-50';
                
                const changeClass = item.change >= 0 ? 'text-green-600' : 'text-red-600';
                const changeSign = item.change >= 0 ? '+' : '';
                
                // Format date
                const date = new Date(item.date);
                const formattedDate = date.toLocaleDateString('en-US', { 
                    month: 'numeric', 
                    day: 'numeric', 
                    year: '2-digit' 
                });
                
                // Format rate
                const formattedRate = parseFloat(item.rate).toFixed(4);
                
                row.innerHTML = `
                    <td class="px-3 py-2 whitespace-nowrap text-xs text-violet-900">${formattedDate}</td>
                    <td class="px-3 py-2 whitespace-nowrap text-xs text-violet-900 font-medium">${formattedRate}</td>
                    <td class="px-3 py-2 whitespace-nowrap text-xs font-semibold ${changeClass}">${changeSign}${item.change}%</td>
                `;
                
                tableBody.appendChild(row);
            });
        }
    }
</script>
@endpush
