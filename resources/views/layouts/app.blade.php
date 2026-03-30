<!DOCTYPE html>
<html lang="en" class="">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Currency Analytics')</title>
    <link rel="icon" type="image/jpeg" href="/logos/newcurrapilogo_nobg.png">

    <script src="https://cdn.tailwindcss.com" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11" defer></script>

    <script>
        // Apply dark mode BEFORE page renders to avoid flash
        (function() {
            if (localStorage.getItem('darkMode') === 'true') {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>

    <style>
        /* ── Light mode (default) ── */
        body {
            background: linear-gradient(135deg, #ffffff 0%, #f5f3ff 25%, #ede9fe 50%, #ddd6fe 75%, #c4b5fd 100%);
            background-attachment: fixed;
            min-height: 100vh;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            color: #1e293b;
            transition: background 0.3s ease, color 0.3s ease;
        }

        /* ── Dark mode ── */
        html.dark body {
            background: linear-gradient(135deg, #0f0a1e 0%, #1a0f2e 25%, #1e1040 50%, #160d35 75%, #0d0820 100%);
            color: #e2e8f0;
        }

        html.dark .glass-surface {
            background: linear-gradient(to bottom right, rgba(20, 10, 40, 0.97), rgba(15, 8, 30, 0.95));
            box-shadow:
                0 25px 50px -12px rgba(0, 0, 0, 0.5),
                0 0 0 1px rgba(139, 92, 246, 0.15);
        }

        html.dark nav {
            background: rgba(15, 10, 30, 0.95) !important;
            border-color: rgba(139, 92, 246, 0.2) !important;
        }

        html.dark footer {
            background: rgba(15, 10, 30, 0.95) !important;
            border-color: rgba(139, 92, 246, 0.2) !important;
        }

        html.dark .bg-white {
            background-color: rgba(25, 15, 50, 0.9) !important;
        }

        html.dark .text-violet-900 {
            color: #ddd6fe !important;
        }

        html.dark .text-violet-700 {
            color: #c4b5fd !important;
        }

        html.dark .text-violet-600 {
            color: #a78bfa !important;
        }

        html.dark .text-violet-500 {
            color: #8b5cf6 !important;
        }

        html.dark .border-violet-100,
        html.dark .border-violet-200 {
            border-color: rgba(139, 92, 246, 0.2) !important;
        }

        html.dark .border-violet-50 {
            border-color: rgba(139, 92, 246, 0.1) !important;
        }

        html.dark .bg-violet-50 {
            background-color: rgba(139, 92, 246, 0.1) !important;
        }

        html.dark .bg-green-50 {
            background-color: rgba(16, 185, 129, 0.1) !important;
        }

        html.dark .bg-red-50 {
            background-color: rgba(239, 68, 68, 0.1) !important;
        }

        html.dark .bg-green-100 {
            background-color: rgba(16, 185, 129, 0.15) !important;
        }

        html.dark .bg-red-100 {
            background-color: rgba(239, 68, 68, 0.15) !important;
        }

        html.dark .text-green-600 {
            color: #34d399 !important;
        }

        html.dark .text-red-600 {
            color: #f87171 !important;
        }

        html.dark .hover\:bg-gray-50:hover {
            background-color: rgba(139, 92, 246, 0.08) !important;
        }

        html.dark select,
        html.dark input[type="number"] {
            background-color: rgba(25, 15, 50, 0.9) !important;
            color: #ddd6fe !important;
            border-color: rgba(139, 92, 246, 0.3) !important;
        }

        html.dark select option {
            background-color: #1a0f2e;
            color: #ddd6fe;
        }

        html.dark .nav-chip {
            color: #c4b5fd !important;
        }

        html.dark .nav-chip:not(.active-nav) {
            background-color: rgba(139, 92, 246, 0.15) !important;
        }

        html.dark .nav-chip:hover {
            background-color: rgba(139, 92, 246, 0.25) !important;
        }

        /* Active nav chip in dark mode — distinct but not bright */
        html.dark .nav-chip.active-nav {
            background-color: rgba(139, 92, 246, 0.5) !important;
            color: #ede9fe !important;
            box-shadow: 0 0 0 1px rgba(139, 92, 246, 0.4) !important;
        }

        /* Multi-currency result cards */
        .currency-result-card {
            background: linear-gradient(to bottom right, #f5f3ff, #ffffff);
        }

        html.dark .currency-result-card {
            background: rgba(25, 12, 52, 0.92) !important;
            border-color: rgba(139, 92, 246, 0.2) !important;
        }

        /* Gradient overrides for dark mode */
        html.dark .from-violet-50 {
            --tw-gradient-from: rgba(30, 15, 60, 0.9) !important;
        }

        html.dark .to-white {
            --tw-gradient-to: rgba(20, 10, 45, 0.95) !important;
        }

        /* Trend badge text in dark mode */
        html.dark .text-green-600 {
            color: #34d399 !important;
        }

        html.dark .text-red-600 {
            color: #f87171 !important;
        }

        /* ── Animations ── */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to   { opacity: 1; }
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-20px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .animate-fadeInUp  { animation: fadeInUp 0.6s ease-out; }
        .animate-fadeIn    { animation: fadeIn 0.5s ease-out; }
        .animate-slideDown { animation: slideDown 0.5s ease-out; }

        .animate-delay-100 { animation-delay: 0.1s; animation-fill-mode: both; }
        .animate-delay-200 { animation-delay: 0.2s; animation-fill-mode: both; }
        .animate-delay-300 { animation-delay: 0.3s; animation-fill-mode: both; }

        .glass-surface {
            background: linear-gradient(to bottom right, rgba(255,255,255,0.95), rgba(248,250,252,0.9));
            backdrop-filter: blur(10px);
            box-shadow:
                0 25px 50px -12px rgba(15,23,42,0.08),
                0 0 0 1px rgba(148,163,184,0.12);
        }

        .nav-chip {
            box-shadow:
                0 10px 25px -5px rgba(15,23,42,0.1),
                0 0 0 1px rgba(148,163,184,0.2);
        }

        /* Mobile nav horizontal scroll */
        .nav-scroll {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none;
        }
        .nav-scroll::-webkit-scrollbar {
            display: none;
        }
    </style>
</head>
<body class="antialiased">
    <div class="min-h-screen flex flex-col">

        <!-- Navigation -->
        <nav class="py-3 md:py-5 border-b border-violet-200 bg-white backdrop-blur-md shadow-md animate-slideDown">
            <div class="container mx-auto px-4">
                <div class="flex items-center justify-between gap-4">

                    <!-- Logo -->
                    <div class="flex-shrink-0">
                        <h1 class="text-violet-900 text-xl md:text-2xl font-bold whitespace-nowrap">
                            Currency Analytics
                        </h1>
                    </div>

                    <!-- Nav links — horizontally scrollable on mobile -->
                    <div class="nav-scroll flex items-center gap-2 flex-1 justify-end pb-0.5">
                        <a href="{{ route('currency.converter') }}"
                           class="nav-chip flex-shrink-0 px-3 md:px-4 py-1.5 md:py-2 rounded-full text-violet-800 text-xs md:text-sm font-medium transition-all duration-200 hover:-translate-y-0.5 {{ request()->routeIs('currency.converter') ? 'bg-violet-300 text-white shadow-lg active-nav' : 'bg-violet-100 hover:bg-violet-200' }}">
                            Converter
                        </a>
                        <a href="{{ route('currency.historical') }}"
                           class="nav-chip flex-shrink-0 px-3 md:px-4 py-1.5 md:py-2 rounded-full text-violet-800 text-xs md:text-sm font-medium transition-all duration-200 hover:-translate-y-0.5 {{ request()->routeIs('currency.historical') ? 'bg-violet-300 text-white shadow-lg active-nav' : 'bg-violet-100 hover:bg-violet-200' }}">
                            Historical
                        </a>
                        <a href="{{ route('currency.trend-analysis') }}"
                           class="nav-chip flex-shrink-0 px-3 md:px-4 py-1.5 md:py-2 rounded-full text-violet-800 text-xs md:text-sm font-medium transition-all duration-200 hover:-translate-y-0.5 {{ request()->routeIs('currency.trend-analysis') ? 'bg-violet-300 text-white shadow-lg active-nav' : 'bg-violet-100 hover:bg-violet-200' }}">
                            Trend Analysis
                        </a>
                        <a href="{{ route('currency.multi-currency') }}"
                           class="nav-chip flex-shrink-0 px-3 md:px-4 py-1.5 md:py-2 rounded-full text-violet-800 text-xs md:text-sm font-medium transition-all duration-200 hover:-translate-y-0.5 {{ request()->routeIs('currency.multi-currency') ? 'bg-violet-300 text-white shadow-lg active-nav' : 'bg-violet-100 hover:bg-violet-200' }}">
                            Multi-Currency
                        </a>
                        <a href="{{ route('currency.dashboard') }}"
                           class="nav-chip flex-shrink-0 px-3 md:px-4 py-1.5 md:py-2 rounded-full text-violet-800 text-xs md:text-sm font-medium transition-all duration-200 hover:-translate-y-0.5 {{ request()->routeIs('currency.dashboard') ? 'bg-violet-300 text-white shadow-lg active-nav' : 'bg-violet-100 hover:bg-violet-200' }}">
                            Dashboard
                        </a>

                        <!-- Dark mode toggle -->
                        <button id="darkModeToggle"
                                title="Toggle dark mode"
                                class="nav-chip flex-shrink-0 w-8 h-8 flex items-center justify-center rounded-full bg-violet-100 hover:bg-violet-200 text-violet-800 transition-all duration-200 hover:-translate-y-0.5">
                            <span id="darkIcon">🌙</span>
                            <span id="lightIcon" class="hidden">☀️</span>
                        </button>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="container mx-auto px-4 py-6 md:py-10 flex-1">
            <div class="glass-surface rounded-[2rem] md:rounded-[2.5rem] px-4 py-5 md:px-8 md:py-8 lg:px-10 lg:py-10">
                @yield('content')
            </div>
        </main>

        <!-- Footer -->
        <footer class="text-center text-violet-900 py-4 md:py-6 border-t border-violet-200 bg-white backdrop-blur-md shadow-md animate-fadeIn">
            <p class="text-xs md:text-sm font-medium">© 2026 Currency Exchange Rate Analytics System.</p>
            <p class="text-xs text-violet-900 mt-1 font-medium">Made by Group 1</p>
        </footer>
    </div>

    @stack('scripts')

    <script>
        // ── Global currency flag map ──
        window.CURRENCY_FLAGS = {
            'USD': '🇺🇸', 'EUR': '🇪🇺', 'GBP': '🇬🇧', 'JPY': '🇯🇵',
            'AUD': '🇦🇺', 'CAD': '🇨🇦', 'CHF': '🇨🇭', 'CNY': '🇨🇳',
            'INR': '🇮🇳', 'PHP': '🇵🇭', 'SGD': '🇸🇬', 'KRW': '🇰🇷',
            'BRL': '🇧🇷', 'MXN': '🇲🇽', 'NZD': '🇳🇿', 'MYR': '🇲🇾',
            'IDR': '🇮🇩', 'THB': '🇹🇭', 'VND': '🇻🇳', 'HKD': '🇭🇰',
            'TWD': '🇹🇼', 'AED': '🇦🇪', 'SAR': '🇸🇦', 'QAR': '🇶🇦',
            'KWD': '🇰🇼'
        };

        window.currencyFlag = function(code) {
            return window.CURRENCY_FLAGS[code] ?? '🏳️';
        };

        // ── Dark mode toggle ──
        const darkToggle = document.getElementById('darkModeToggle');
        const darkIcon   = document.getElementById('darkIcon');
        const lightIcon  = document.getElementById('lightIcon');
        const htmlEl     = document.documentElement;

        function applyDarkMode(isDark) {
            if (isDark) {
                htmlEl.classList.add('dark');
                darkIcon.classList.add('hidden');
                lightIcon.classList.remove('hidden');
            } else {
                htmlEl.classList.remove('dark');
                darkIcon.classList.remove('hidden');
                lightIcon.classList.add('hidden');
            }
            localStorage.setItem('darkMode', isDark);
        }

        // Set initial icon state based on saved preference
        const savedDark = localStorage.getItem('darkMode') === 'true';
        applyDarkMode(savedDark);

        darkToggle.addEventListener('click', () => {
            const isCurrentlyDark = htmlEl.classList.contains('dark');
            applyDarkMode(!isCurrentlyDark);
        });
    </script>
</body>
</html>