<?php

namespace App\Http\Controllers;

use App\Services\WeatherService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class WeatherController extends Controller
{
    public function __construct(
        private WeatherService $weatherService
    ) {}

    public function index(Request $request): Response
    {
        $city = $request->get('city', 'Tallinn');
        $weather = null;
        $forecast = null;
        $error = null;

        try {
            $rawWeather = $this->weatherService->getCurrentWeather($city);
            $weather = $this->weatherService->formatWeatherData($rawWeather);

            $rawForecast = $this->weatherService->getForecast($city);
            $forecast = $this->weatherService->formatForecastData($rawForecast);
        } catch (\Exception $e) {
            $error = "Linna '{$city}' ilmaandmeid ei leitud. Kontrolli linnanime.";
        }

        $popularCities = [
            'Tallinn', 'Tartu', 'Narva', 'Pärnu', 'Kohtla-Järve',
            'Helsinki', 'Riga', 'Vilnius', 'Stockholm', 'Oslo',
        ];

        return Inertia::render('Weather/Index', [
            'weather'       => $weather,
            'forecast'      => $forecast,
            'currentCity'   => $city,
            'popularCities' => $popularCities,
            'error'         => $error,
        ]);
    }

    public function search(Request $request)
    {
        $request->validate(['city' => 'required|string|max:100']);
        return redirect()->route('weather.index', ['city' => $request->city]);
    }

    public function apiWeather(Request $request)
    {
        $request->validate(['city' => 'required|string|max:100']);

        try {
            $raw = $this->weatherService->getCurrentWeather($request->city);
            $forecast_raw = $this->weatherService->getForecast($request->city);

            return response()->json([
                'success'  => true,
                'weather'  => $this->weatherService->formatWeatherData($raw),
                'forecast' => $this->weatherService->formatForecastData($forecast_raw),
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 404);
        }
    }
}
