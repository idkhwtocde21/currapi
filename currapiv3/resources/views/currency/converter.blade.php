@extends('layouts.app')

@section('title', 'Currency Converter - Currency Analytics')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="animate-fadeInUp">
        <!-- Header -->
        <div class="bg-gradient-to-r from-violet-300 to-violet-400 backdrop-blur-sm rounded-2xl shadow-2xl p-6 md:p-8 mb-4 md:mb-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h2 class="text-2xl md:text-4xl font-bold text-white mb-2">Currency Converter</h2>
                    <p class="text-sm md:text-base text-violet-50"></p>
                </div>
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-emerald-500/10 border border-emerald-300/60">
                    <span class="inline-block h-2 w-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span class="text-[11px] font-medium text-emerald-200 uppercase tracking-wide">Live rates</span>
                </div>
            </div>
        </div>

        <!-- Converter Form -->
        <div class="bg-white backdrop-blur-sm rounded-xl shadow-lg p-6 md:p-8 border border-violet-100">
            <form id="converterForm" class="space-y-4 md:space-y-6" novalidate>
            @csrf
            
            <!-- Amount Input -->
            <div>
                <label for="amount" class="block text-violet-700 font-medium mb-2 text-sm md:text-base">Amount</label>
                <input type="number" 
                       id="amount" 
                       name="amount" 
                       value="1" 
                       step="0.01"
                       min="0"
                       max="1000000"
                       class="w-full px-4 py-3 rounded-xl border border-violet-200 bg-white text-violet-900 placeholder:text-violet-400 shadow-sm focus:ring-2 focus:ring-violet-300 focus:border-violet-300 outline-none transition-all"
                       required>
                <p class="mt-1 text-xs text-violet-500">Maximum amount: 1,000,000</p>
            </div>

            <!-- Currency Selection -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="from" class="block text-violet-700 font-medium mb-2 text-sm md:text-base">From</label>
                    <select id="from" 
                            name="from" 
                            class="w-full px-4 py-3 rounded-xl border border-violet-200 bg-white text-violet-900 shadow-sm focus:ring-2 focus:ring-violet-300 focus:border-violet-300 outline-none transition-all">
                        @foreach($currencies as $code => $name)
                            <option value="{{ $code }}" {{ $code === 'USD' ? 'selected' : '' }}>
                                {{ $code }} - {{ $name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="to" class="block text-violet-700 font-medium mb-2 text-sm md:text-base">To</label>
                    <select id="to" 
                            name="to" 
                            class="w-full px-4 py-3 rounded-xl border border-violet-200 bg-white text-violet-900 shadow-sm focus:ring-2 focus:ring-violet-300 focus:border-violet-300 outline-none transition-all">
                        @foreach($currencies as $code => $name)
                            <option value="{{ $code }}" {{ $code === 'PHP' ? 'selected' : '' }}>
                                {{ $code }} - {{ $name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Swap Button -->
            <div class="flex justify-center">
                <button type="button" 
                        id="swapBtn" 
                        class="flex items-center gap-2 px-5 py-2 bg-violet-200 text-violet-700 rounded-full hover:bg-violet-300 transition-all duration-200 shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                    </svg>
                    Swap
                </button>
            </div>

            <!-- Convert Button -->
            <div class="space-y-3 md:space-y-4">
                <button type="submit" 
                        id="convertBtn"
                        class="w-full inline-flex items-center justify-center gap-2 py-3 md:py-3.5 bg-gradient-to-r from-violet-300 to-violet-400 text-white font-semibold text-sm md:text-base rounded-xl hover:from-violet-400 hover:to-violet-500 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-violet-400 disabled:cursor-not-allowed disabled:opacity-70">
                    <span id="convertBtnText">Convert</span>
                    <span id="convertBtnSpinner" class="hidden h-4 w-4 border-2 border-white/40 border-t-white rounded-full animate-spin"></span>
                </button>

                <p id="converterMessage" class="hidden text-xs md:text-sm text-center text-violet-600"></p>
            </div>
        </form>
        </div>

        <!-- Result -->
        <div id="result" class="mt-6 md:mt-8 p-4 md:p-6 bg-gradient-to-br from-violet-300 to-violet-400 border border-violet-200 rounded-2xl shadow-xl hidden animate-fadeInUp">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 md:gap-4">
                <div>
                    <p class="text-xs font-medium tracking-[0.25em] uppercase text-violet-50 mb-1">Conversion Result</p>
                    <p class="text-3xl md:text-4xl font-extrabold text-white" id="resultAmount"></p>
                    <p class="text-violet-50 mt-1 text-xs md:text-sm" id="resultRate"></p>
                </div>
                <div class="flex flex-col items-start md:items-end gap-1 text-xs text-violet-50">
                    <p>Rates are indicative and may differ from your bank or provider.</p>
                    <p class="italic">Powered by your currency analytics backend.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const form = document.getElementById('converterForm');
    const swapBtn = document.getElementById('swapBtn');
    const resultDiv = document.getElementById('result');
    const resultAmount = document.getElementById('resultAmount');
    const resultRate = document.getElementById('resultRate');
    const convertBtn = document.getElementById('convertBtn');
    const convertBtnText = document.getElementById('convertBtnText');
    const convertBtnSpinner = document.getElementById('convertBtnSpinner');
    const converterMessage = document.getElementById('converterMessage');

    // Swap currencies
    swapBtn.addEventListener('click', () => {
        const fromSelect = document.getElementById('from');
        const toSelect = document.getElementById('to');
        
        const temp = fromSelect.value;
        fromSelect.value = toSelect.value;
        toSelect.value = temp;
    });

    // Handle form submission
    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        // Basic validation
        const amountValue = parseFloat(document.getElementById('amount').value);
        if (isNaN(amountValue) || amountValue <= 0) {
            Swal.fire({
                icon: 'error',
                title: 'Invalid Amount',
                text: 'Please enter an amount greater than 0.',
                confirmButtonColor: '#374151',
                timer: 3000
            });
            return;
        }

        if (amountValue > 1000000) {
            Swal.fire({
                icon: 'error',
                title: 'Amount Too Large',
                text: 'Amount cannot exceed 1,000,000. Please enter a smaller amount.',
                confirmButtonColor: '#374151',
                timer: 3000
            });
            return;
        }

        setLoading(true);
        showMessage('', 'clear');
        
        const formData = new FormData(form);
        const data = {
            amount: amountValue,
            from: formData.get('from'),
            to: formData.get('to')
        };

        try {
            const response = await fetch('{{ route('currency.convert') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (!response.ok || !result) {
                throw new Error(result?.message || 'Unable to perform conversion right now.');
            }

            if (result && typeof result.result === 'number' && typeof result.rate === 'number') {
                resultAmount.textContent = `${result.result.toFixed(2)} ${result.to}`;
                resultRate.textContent = `1 ${result.from} = ${result.rate.toFixed(4)} ${result.to}`;
                resultDiv.classList.remove('hidden');
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'Conversion completed successfully!',
                    confirmButtonColor: '#374151',
                    timer: 3000,
                    showConfirmButton: false
                });
            }
        } catch (error) {
            console.error('Conversion error:', error);
            const errorMessage = error.message || 'An error occurred during conversion. Please try again in a moment.';
            Swal.fire({
                icon: 'error',
                title: 'Conversion Failed',
                text: errorMessage,
                confirmButtonColor: '#374151',
                timer: 3000
            });
            resultDiv.classList.add('hidden');
        } finally {
            setLoading(false);
        }
    });

    function setLoading(isLoading) {
        if (!convertBtn) return;
        convertBtn.disabled = isLoading;
        if (isLoading) {
            convertBtnText.textContent = 'Converting...';
            convertBtnSpinner.classList.remove('hidden');
        } else {
            convertBtnText.textContent = 'Convert';
            convertBtnSpinner.classList.add('hidden');
        }
    }

    function showMessage(message, type) {
        if (!converterMessage) return;

        if (type === 'clear' || !message) {
            converterMessage.classList.add('hidden');
            converterMessage.textContent = '';
            converterMessage.className = 'hidden text-xs md:text-sm text-center';
            return;
        }

        converterMessage.textContent = message;
        converterMessage.classList.remove('hidden');
        converterMessage.className = 'text-xs md:text-sm text-center mt-1';

        if (type === 'error') {
            converterMessage.classList.add('text-red-500');
        } else if (type === 'success') {
            converterMessage.classList.add('text-emerald-500');
        } else {
            converterMessage.classList.add('text-slate-400');
        }
    }
</script>
@endpush
