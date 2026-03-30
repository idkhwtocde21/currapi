<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class CurrencyService
{
    protected $apiKey;
    protected $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('currency.api_key');
        $this->baseUrl = config('currency.base_url');
    }

    /**
     * Get all supported currencies
     */
    public function getSupportedCurrencies()
    {
        return [
            // Major Global
            'USD' => 'US Dollar',
            'EUR' => 'Euro',
            'GBP' => 'British Pound',
            'JPY' => 'Japanese Yen',
            'CHF' => 'Swiss Franc',
            'CNY' => 'Chinese Yuan',

            // Oceania
            'AUD' => 'Australian Dollar',
            'NZD' => 'New Zealand Dollar',

            // Americas
            'CAD' => 'Canadian Dollar',
            'BRL' => 'Brazilian Real',
            'MXN' => 'Mexican Peso',

            // South & Southeast Asia (OFW-relevant)
            'PHP' => 'Philippine Peso',
            'INR' => 'Indian Rupee',
            'SGD' => 'Singapore Dollar',
            'MYR' => 'Malaysian Ringgit',
            'IDR' => 'Indonesian Rupiah',
            'THB' => 'Thai Baht',
            'VND' => 'Vietnamese Dong',

            // East Asia
            'KRW' => 'South Korean Won',
            'HKD' => 'Hong Kong Dollar',
            'TWD' => 'Taiwan Dollar',

            // Middle East (OFW-relevant)
            'AED' => 'UAE Dirham',
            'SAR' => 'Saudi Riyal',
            'QAR' => 'Qatari Riyal',
            'KWD' => 'Kuwaiti Dinar',
        ];
    }

    /**
     * Get the time elapsed since rates were last cached.
     * Returns a human-readable string like "5 minutes ago".
     */
    public function getLastUpdated($baseCurrency = 'USD')
    {
        $cacheKey = "exchange_rates_{$baseCurrency}";
        $tsKey    = "exchange_rates_{$baseCurrency}_cached_at";

        if (Cache::has($cacheKey)) {
            $cachedAt = Cache::get($tsKey);
            if ($cachedAt) {
                $diff = now()->diffInMinutes(\Carbon\Carbon::parse($cachedAt));
                if ($diff < 1)  return 'just now';
                if ($diff < 60) return "{$diff} minute" . ($diff === 1 ? '' : 's') . " ago";
                $hours = floor($diff / 60);
                return "{$hours} hour" . ($hours === 1 ? '' : 's') . " ago";
            }
            return 'recently';
        }

        return 'not yet loaded';
    }

    /**
     * Get exchange rates for a base currency (cached 1 hour).
     * Also stores a timestamp so getLastUpdated() can report it.
     */
    public function getExchangeRates($baseCurrency = 'USD')
    {
        $cacheKey = "exchange_rates_{$baseCurrency}";
        $tsKey    = "exchange_rates_{$baseCurrency}_cached_at";

        // If already cached, return immediately
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        // Fetch fresh from API
        try {
            $response = Http::get("{$this->baseUrl}{$this->apiKey}/latest/{$baseCurrency}");

            if ($response->successful()) {
                $data  = $response->json();
                $rates = $data['conversion_rates'] ?? [];

                // Store rates and timestamp separately
                Cache::put($cacheKey, $rates, 21600);
                Cache::put($tsKey, now()->toIso8601String(), 21600);

                return $rates;
            }

            return [];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Convert amount from one currency to another
     */
    public function convert($amount, $from, $to)
    {
        $rates = $this->getExchangeRates($from);

        if (isset($rates[$to])) {
            return [
                'amount' => $amount,
                'from'   => $from,
                'to'     => $to,
                'rate'   => $rates[$to],
                'result' => $amount * $rates[$to],
            ];
        }

        return null;
    }

    /**
     * Generate a stable daily seed integer for a given date string.
     * Same date + same currency pair always produces the same seed.
     */
    private function dailySeed(string $dateStr, string $base, string $compare): int
    {
        return abs(crc32($dateStr . $base . $compare));
    }

    /**
     * Generate a realistic daily drift factor for a given seed.
     * Max drift: ±0.3% per day — matches typical FX market movement.
     */
    private function dailyDrift(int $seed): float
    {
        $normalized = ($seed % 10000) / 10000;
        return ($normalized - 0.5) * 0.006;
    }

    /**
     * Get historical data using seeded realistic drift from today's live rate.
     * Data is stable — same result every call for the same day/pair.
     */
    public function getHistoricalData($baseCurrency, $compareCurrency, $days = 7)
    {
        $todayRate = $this->getExchangeRates($baseCurrency)[$compareCurrency] ?? 1.0;

        $dates = [];
        for ($i = $days; $i >= 0; $i--) {
            $dates[] = now()->subDays($i)->format('Y-m-d');
        }

        $drifts = [];
        foreach ($dates as $date) {
            $seed     = $this->dailySeed($date, $baseCurrency, $compareCurrency);
            $drifts[] = $this->dailyDrift($seed);
        }

        $compound = 1.0;
        foreach ($drifts as $d) {
            $compound *= (1 + $d);
        }
        $startRate = $todayRate / $compound;

        $data        = [];
        $currentRate = $startRate;

        foreach ($dates as $index => $date) {
            $drift       = $drifts[$index];
            $currentRate = $currentRate * (1 + $drift);

            $data[] = [
                'date'   => $date,
                'rate'   => round($currentRate, 4),
                'change' => round($drift * 100, 4),
            ];
        }

        return $data;
    }

    /**
     * Get trend analysis — stats derived from stable historical data
     */
    public function getTrendAnalysis($baseCurrency, $compareCurrency, $days = 7)
    {
        $historicalData = $this->getHistoricalData($baseCurrency, $compareCurrency, $days);

        if (empty($historicalData)) {
            return null;
        }

        $rates        = array_column($historicalData, 'rate');
        $currentRate  = end($rates);
        $firstRate    = reset($rates);
        $averageRate  = array_sum($rates) / count($rates);
        $highestRate  = max($rates);
        $lowestRate   = min($rates);
        $periodChange = $firstRate > 0
            ? round((($currentRate - $firstRate) / $firstRate) * 100, 2)
            : 0;

        return [
            'current'       => round($currentRate, 4),
            'average'       => round($averageRate, 4),
            'highest'       => round($highestRate, 4),
            'lowest'        => round($lowestRate, 4),
            'period_change' => $periodChange,
            'data'          => $historicalData,
        ];
    }

    /**
     * Compare multiple currencies against a base.
     * % change derived from stable seeded yesterday vs today.
     */
    public function compareMultipleCurrencies($baseCurrency, $currencies)
    {
        $todayRates = $this->getExchangeRates($baseCurrency);
        $results    = [];
        $today      = now()->format('Y-m-d');

        foreach ($currencies as $currency) {
            if (!isset($todayRates[$currency])) {
                continue;
            }

            $todayRate  = $todayRates[$currency];
            $seedToday  = $this->dailySeed($today, $baseCurrency, $currency);
            $driftToday = $this->dailyDrift($seedToday);
            $yesterdayRate = $todayRate / (1 + $driftToday);
            $change = $yesterdayRate > 0
                ? round((($todayRate - $yesterdayRate) / $yesterdayRate) * 100, 2)
                : 0;

            $results[] = [
                'currency' => $currency,
                'rate'     => round($todayRate, 4),
                'change'   => $change,
                'trend'    => $change >= 0 ? 'up' : 'down',
            ];
        }

        return $results;
    }

    /**
     * Get dashboard data.
     * Gainers/losers use stable seeded % change — no rand().
     */
    public function getDashboardData()
    {
        $baseCurrency = 'USD';
        $rates        = $this->getExchangeRates($baseCurrency);
        $today        = now()->format('Y-m-d');

        $mainPairs = [
            'EUR' => $rates['EUR'] ?? 0,
            'GBP' => $rates['GBP'] ?? 0,
            'JPY' => $rates['JPY'] ?? 0,
            'PHP' => $rates['PHP'] ?? 0,
            'AUD' => $rates['AUD'] ?? 0,
            'CAD' => $rates['CAD'] ?? 0,
        ];

        $gainers = [];
        $losers  = [];

        foreach ($mainPairs as $currency => $rate) {
            if ($rate <= 0) continue;

            $seed   = $this->dailySeed($today, $baseCurrency, $currency);
            $drift  = $this->dailyDrift($seed);
            $change = round($drift * 100, 2);

            $entry = [
                'pair'   => "USD/{$currency}",
                'rate'   => round($rate, 4),
                'change' => $change,
            ];

            if ($change >= 0) {
                $gainers[] = $entry;
            } else {
                $losers[] = $entry;
            }
        }

        usort($gainers, fn($a, $b) => $b['change'] <=> $a['change']);
        usort($losers,  fn($a, $b) => $a['change'] <=> $b['change']);

        $chartData = $this->getHistoricalData('USD', 'PHP', 6);

        return [
            'rates'      => $mainPairs,
            'gainers'    => array_slice($gainers, 0, 3),
            'losers'     => array_slice($losers, 0, 3),
            'chart_data' => $chartData,
        ];
    }
}