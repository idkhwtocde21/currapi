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
            'USD' => 'US Dollar',
            'EUR' => 'Euro',
            'GBP' => 'British Pound',
            'JPY' => 'Japanese Yen',
            'AUD' => 'Australian Dollar',
            'CAD' => 'Canadian Dollar',
            'CHF' => 'Swiss Franc',
            'CNY' => 'Chinese Yuan',
            'INR' => 'Indian Rupee',
            'PHP' => 'Philippine Peso',
            'SGD' => 'Singapore Dollar',
            'KRW' => 'South Korean Won',
            'BRL' => 'Brazilian Real',
            'MXN' => 'Mexican Peso',
            'NZD' => 'New Zealand Dollar',
        ];
    }

    /**
     * Get exchange rates for a base currency
     */
    public function getExchangeRates($baseCurrency = 'USD')
    {
        $cacheKey = "exchange_rates_{$baseCurrency}";
        
        return Cache::remember($cacheKey, 3600, function () use ($baseCurrency) {
            try {
                $response = Http::get("{$this->baseUrl}{$this->apiKey}/latest/{$baseCurrency}");
                
                if ($response->successful()) {
                    $data = $response->json();
                    return $data['conversion_rates'] ?? [];
                }
                
                return [];
            } catch (\Exception $e) {
                return [];
            }
        });
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
                'from' => $from,
                'to' => $to,
                'rate' => $rates[$to],
                'result' => $amount * $rates[$to],
            ];
        }
        
        return null;
    }

    /**
     * Get historical data (simulated with random variations)
     */
    public function getHistoricalData($baseCurrency, $compareCurrency, $days = 7)
    {
        $currentRate = $this->getExchangeRates($baseCurrency)[$compareCurrency] ?? 1;
        $data = [];
        
        for ($i = $days; $i >= 0; $i--) {
            $date = now()->subDays($i);
            // Simulate historical rates with slight variations
            $variation = (rand(-500, 500) / 10000); // -5% to +5% variation
            $rate = $currentRate * (1 + $variation);
            
            $data[] = [
                'date' => $date->format('Y-m-d'),
                'rate' => round($rate, 4),
                'change' => round($variation * 100, 2),
            ];
        }
        
        return $data;
    }

    /**
     * Get trend analysis data
     */
    public function getTrendAnalysis($baseCurrency, $compareCurrency, $days = 7)
    {
        $historicalData = $this->getHistoricalData($baseCurrency, $compareCurrency, $days);
        
        if (empty($historicalData)) {
            return null;
        }
        
        $rates = array_column($historicalData, 'rate');
        $currentRate = end($rates);
        $averageRate = array_sum($rates) / count($rates);
        $highestRate = max($rates);
        $lowestRate = min($rates);
        
        return [
            'current' => round($currentRate, 4),
            'average' => round($averageRate, 4),
            'highest' => round($highestRate, 4),
            'lowest' => round($lowestRate, 4),
            'data' => $historicalData,
        ];
    }

    /**
     * Get multiple currency comparisons
     */
    public function compareMultipleCurrencies($baseCurrency, $currencies)
    {
        $rates = $this->getExchangeRates($baseCurrency);
        $results = [];
        
        foreach ($currencies as $currency) {
            if (isset($rates[$currency])) {
                $rate = $rates[$currency];
                $change = (rand(-200, 200) / 100); // Simulated change percentage
                
                $results[] = [
                    'currency' => $currency,
                    'rate' => round($rate, 4),
                    'change' => $change,
                    'trend' => $change > 0 ? 'up' : 'down',
                ];
            }
        }
        
        return $results;
    }

    /**
     * Get dashboard data
     */
    public function getDashboardData()
    {
        $baseCurrency = 'USD';
        $rates = $this->getExchangeRates($baseCurrency);
        
        $mainPairs = [
            'EUR' => $rates['EUR'] ?? 0,
            'GBP' => $rates['GBP'] ?? 0,
            'JPY' => $rates['JPY'] ?? 0,
            'PHP' => $rates['PHP'] ?? 0,
            'AUD' => $rates['AUD'] ?? 0,
            'CAD' => $rates['CAD'] ?? 0,
        ];
        
        // Generate gainers and losers
        $gainers = [];
        $losers = [];
        
        foreach ($mainPairs as $currency => $rate) {
            $change = (rand(-300, 300) / 100);
            
            if ($change > 0) {
                $gainers[] = [
                    'pair' => "USD/{$currency}",
                    'rate' => round($rate, 4),
                    'change' => $change,
                ];
            } else {
                $losers[] = [
                    'pair' => "USD/{$currency}",
                    'rate' => round($rate, 4),
                    'change' => $change,
                ];
            }
        }
        
        // Sort by change
        usort($gainers, fn($a, $b) => $b['change'] <=> $a['change']);
        usort($losers, fn($a, $b) => $a['change'] <=> $b['change']);
        
        return [
            'rates' => $mainPairs,
            'gainers' => array_slice($gainers, 0, 3),
            'losers' => array_slice($losers, 0, 3),
        ];
    }
}
