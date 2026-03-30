<?php

namespace App\Http\Controllers;

use App\Services\CurrencyService;
use Illuminate\Http\Request;

class CurrencyController extends Controller
{
    protected $currencyService;

    public function __construct(CurrencyService $currencyService)
    {
        $this->currencyService = $currencyService;
    }

    /**
     * Show the converter page
     */
    public function converter()
    {
        $currencies  = $this->currencyService->getSupportedCurrencies();
        $lastUpdated = $this->currencyService->getLastUpdated('USD');
        return view('currency.converter', compact('currencies', 'lastUpdated'));
    }

    /**
     * Perform currency conversion
     */
    public function convert(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0|max:1000000',
            'from'   => 'required|string',
            'to'     => 'required|string',
        ], [
            'amount.max' => 'Amount cannot exceed 1,000,000.',
            'amount.min' => 'Amount must be greater than 0.',
        ]);

        $result = $this->currencyService->convert(
            $request->amount,
            $request->from,
            $request->to
        );

        return response()->json($result);
    }

    /**
     * Show historical data page
     */
    public function historical()
    {
        $currencies = $this->currencyService->getSupportedCurrencies();
        return view('currency.historical', compact('currencies'));
    }

    /**
     * Get historical data
     */
    public function getHistorical(Request $request)
    {
        $request->validate([
            'base'    => 'required|string',
            'compare' => 'required|string',
            'days'    => 'required|integer|min:1|max:365',
        ]);

        $data = $this->currencyService->getHistoricalData(
            $request->base,
            $request->compare,
            $request->days
        );

        return response()->json($data);
    }

    /**
     * Show trend analysis page
     */
    public function trendAnalysis()
    {
        $currencies = $this->currencyService->getSupportedCurrencies();
        return view('currency.trend-analysis', compact('currencies'));
    }

    /**
     * Get trend analysis data
     */
    public function getTrend(Request $request)
    {
        $request->validate([
            'base'    => 'required|string',
            'compare' => 'required|string',
            'days'    => 'required|integer|min:1|max:365',
        ]);

        $data = $this->currencyService->getTrendAnalysis(
            $request->base,
            $request->compare,
            $request->days
        );

        return response()->json($data);
    }

    /**
     * Show multi-currency comparison page
     */
    public function multiCurrency()
    {
        $currencies = $this->currencyService->getSupportedCurrencies();
        return view('currency.multi-currency', compact('currencies'));
    }

    /**
     * Compare multiple currencies
     */
    public function compareMultiple(Request $request)
    {
        $request->validate([
            'base'          => 'required|string',
            'currencies'    => 'required|array|min:1',
            'currencies.*'  => 'string',
        ]);

        $data = $this->currencyService->compareMultipleCurrencies(
            $request->base,
            $request->currencies
        );

        return response()->json($data);
    }

    /**
     * Show dashboard page
     */
    public function dashboard()
    {
        $data = $this->currencyService->getDashboardData();
        return view('currency.dashboard', $data);
    }

    /**
     * Get live rates for dashboard (AJAX refresh)
     */
    public function getLiveRates()
    {
        $data = $this->currencyService->getDashboardData();
        return response()->json($data);
    }
}