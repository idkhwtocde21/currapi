<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Currency Analytics')</title>
    <link rel="icon" type="image/jpeg" href="/logos/newcurrapilogo_nobg.png">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        body {
            background: linear-gradient(135deg, #ffffff 0%, #f5f3ff 25%, #ede9fe 50%, #ddd6fe 75%, #c4b5fd 100%);
            background-attachment: fixed;
            min-height: 100vh;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            color: #1e293b;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .animate-fadeInUp {
            animation: fadeInUp 0.6s ease-out;
        }
        
        .animate-fadeIn {
            animation: fadeIn 0.5s ease-out;
        }
        
        .animate-slideDown {
            animation: slideDown 0.5s ease-out;
        }
        
        .animate-delay-100 {
            animation-delay: 0.1s;
            animation-fill-mode: both;
        }
        
        .animate-delay-200 {
            animation-delay: 0.2s;
            animation-fill-mode: both;
        }
        
        .animate-delay-300 {
            animation-delay: 0.3s;
            animation-fill-mode: both;
        }
        .glass-surface {
            background: linear-gradient(to bottom right, rgba(255, 255, 255, 0.95), rgba(248, 250, 252, 0.9));
            backdrop-filter: blur(10px);
            box-shadow:
                0 25px 50px -12px rgba(15, 23, 42, 0.08),
                0 0 0 1px rgba(148, 163, 184, 0.12);
        }

        .nav-chip {
            box-shadow:
                0 10px 25px -5px rgba(15, 23, 42, 0.1),
                0 0 0 1px rgba(148, 163, 184, 0.2);
        }
    </style>
</head>
<body class="antialiased">
    <div class="min-h-screen flex flex-col">
        <!-- Navigation -->
        <nav class="py-4 md:py-6 border-b border-violet-200 bg-white backdrop-blur-md shadow-md animate-slideDown">
            <div class="container mx-auto px-4">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 md:gap-6">
                    <div class="text-center md:text-left">
                        <h1 class="text-violet-900 text-2xl md:text-3xl font-bold">
                            Currency Analytics
                        </h1>
                    </div>

                    <div class="flex flex-wrap justify-center md:justify-end gap-2 md:gap-3 pb-1 md:pb-0">
                    <a href="{{ route('currency.converter') }}" 
                       class="nav-chip px-3 md:px-4 py-1.5 md:py-2 rounded-full text-violet-800 text-xs md:text-sm font-medium transition-all duration-200 hover:-translate-y-0.5 hover:bg-violet-200 {{ request()->routeIs('currency.converter') ? 'bg-violet-300 text-white shadow-lg' : 'bg-violet-100 hover:bg-violet-200' }}">
                        Converter
                    </a>
                    <a href="{{ route('currency.historical') }}" 
                       class="nav-chip px-3 md:px-4 py-1.5 md:py-2 rounded-full text-violet-800 text-xs md:text-sm font-medium transition-all duration-200 hover:-translate-y-0.5 hover:bg-violet-200 {{ request()->routeIs('currency.historical') ? 'bg-violet-300 text-white shadow-lg' : 'bg-violet-100 hover:bg-violet-200' }}">
                        Historical Data
                    </a>
                    <a href="{{ route('currency.trend-analysis') }}" 
                       class="nav-chip px-3 md:px-4 py-1.5 md:py-2 rounded-full text-violet-800 text-xs md:text-sm font-medium transition-all duration-200 hover:-translate-y-0.5 hover:bg-violet-200 {{ request()->routeIs('currency.trend-analysis') ? 'bg-violet-300 text-white shadow-lg' : 'bg-violet-100 hover:bg-violet-200' }}">
                        Trend Analysis
                    </a>
                    <a href="{{ route('currency.multi-currency') }}" 
                       class="nav-chip px-3 md:px-4 py-1.5 md:py-2 rounded-full text-violet-800 text-xs md:text-sm font-medium transition-all duration-200 hover:-translate-y-0.5 hover:bg-violet-200 {{ request()->routeIs('currency.multi-currency') ? 'bg-violet-300 text-white shadow-lg' : 'bg-violet-100 hover:bg-violet-200' }}">
                        Multi-Currency
                    </a>
                    <a href="{{ route('currency.dashboard') }}" 
                       class="nav-chip px-3 md:px-4 py-1.5 md:py-2 rounded-full text-violet-800 text-xs md:text-sm font-medium transition-all duration-200 hover:-translate-y-0.5 hover:bg-violet-200 {{ request()->routeIs('currency.dashboard') ? 'bg-violet-300 text-white shadow-lg' : 'bg-violet-100 hover:bg-violet-200' }}">
                        Dashboard
                    </a>
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
</body>
</html>
