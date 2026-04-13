<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WeatherService
{
    private string $apiKey;
    private string $baseUrl = 'https://api.openweathermap.org/data/2.5';
    private int $cacheTtl = 1800; // 30 minutit

    public function __construct()
    {
        $this->apiKey = config('services.openweather.key');

        if (empty($this->apiKey)) {
            \Log::error('OpenWeatherMap API key is not configured');
        }
    }

    public function getCurrentWeather(string $city, string $units = 'metric'): array
    {
        $cacheKey = "weather_current_{$city}_{$units}";

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($city, $units) {
            $response = Http::timeout(10)->get("{$this->baseUrl}/weather", [
                'q'     => $city,
                'appid' => $this->apiKey,
                'units' => $units,
                'lang'  => 'et',
            ]);

            \Log::debug('OpenWeatherMap request', [
                'url'    => "{$this->baseUrl}/weather",
                'city'   => $city,
                'key_length' => strlen($this->apiKey ?? ''),
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            if ($response->failed()) {
                $body = $response->json();
                if ($response->status() === 401) {
                    \Log::error('OpenWeatherMap: Invalid API key', ['message' => $body['message'] ?? '']);
                    throw new \Exception('OpenWeatherMap API key is invalid or expired');
                }
                throw new \Exception($body['message'] ?? "HTTP {$response->status()}");
            }

            return $response->json();
        });
    }

    public function getForecast(string $city, string $units = 'metric'): array
    {
        $cacheKey = "weather_forecast_{$city}_{$units}";

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($city, $units) {
            $response = Http::timeout(10)->get("{$this->baseUrl}/forecast", [
                'q'     => $city,
                'appid' => $this->apiKey,
                'units' => $units,
                'lang'  => 'et',
                'cnt'   => 40,
            ]);

            if ($response->failed()) {
                throw new \Exception("Prognoosi hankimine ebaõnnestus linnale: {$city}");
            }

            return $response->json();
        });
    }

    public function getWeatherByCoords(float $lat, float $lon, string $units = 'metric'): array
    {
        $cacheKey = "weather_coords_{$lat}_{$lon}_{$units}";

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($lat, $lon, $units) {
            $response = Http::timeout(10)->get("{$this->baseUrl}/weather", [
                'lat'   => $lat,
                'lon'   => $lon,
                'appid' => $this->apiKey,
                'units' => $units,
                'lang'  => 'et',
            ]);

            if ($response->failed()) {
                throw new \Exception('Koordinaatide järgi ilmaandmete hankimine ebaõnnestus.');
            }

            return $response->json();
        });
    }

    public function formatWeatherData(array $data): array
    {
        if (empty($data) || isset($data['cod']) && $data['cod'] != 200) {
            throw new \Exception('Invalid weather data response from API');
        }

        return [
            'city'        => $data['name'] ?? 'Tundmatu',
            'country'     => $data['sys']['country'] ?? '',
            'temp'        => round($data['main']['temp'] ?? 0),
            'feels_like'  => round($data['main']['feels_like'] ?? 0),
            'temp_min'    => round($data['main']['temp_min'] ?? 0),
            'temp_max'    => round($data['main']['temp_max'] ?? 0),
            'humidity'    => $data['main']['humidity'] ?? 0,
            'pressure'    => $data['main']['pressure'] ?? 0,
            'wind_speed'  => round($data['wind']['speed'] ?? 0, 1),
            'wind_deg'    => $data['wind']['deg'] ?? 0,
            'visibility'  => ($data['visibility'] ?? 0) / 1000,
            'description' => $data['weather'][0]['description'] ?? '',
            'icon'        => $data['weather'][0]['icon'] ?? '01d',
            'icon_url'    => 'https://openweathermap.org/img/wn/' . ($data['weather'][0]['icon'] ?? '01d') . '@2x.png',
            'sunrise'     => date('H:i', $data['sys']['sunrise'] ?? 0),
            'sunset'      => date('H:i', $data['sys']['sunset'] ?? 0),
            'lat'         => $data['coord']['lat'] ?? 0,
            'lon'         => $data['coord']['lon'] ?? 0,
            'timestamp'   => now()->toISOString(),
        ];
    }

    public function formatForecastData(array $data): array
    {
        $daily = [];
        $grouped = [];

        foreach ($data['list'] as $item) {
            $date = date('Y-m-d', $item['dt']);
            $grouped[$date][] = $item;
        }

        foreach ($grouped as $date => $items) {
            $temps = array_column(array_column($items, 'main'), 'temp');
            $daily[] = [
                'date'        => $date,
                'date_label'  => $this->estonianDate($date),
                'temp_min'    => round(min($temps)),
                'temp_max'    => round(max($temps)),
                'description' => $items[0]['weather'][0]['description'],
                'icon'        => $items[0]['weather'][0]['icon'],
                'icon_url'    => 'https://openweathermap.org/img/wn/' . $items[0]['weather'][0]['icon'] . '@2x.png',
                'humidity'    => round(array_sum(array_column(array_column($items, 'main'), 'humidity')) / count($items)),
                'wind_speed'  => round(array_sum(array_column(array_column($items, 'wind'), 'speed')) / count($items), 1),
            ];
        }

        return array_slice($daily, 0, 5);
    }

    private function estonianDate(string $date): string
    {
        $days = ['Pühapäev', 'Esmaspäev', 'Teisipäev', 'Kolmapäev', 'Neljapäev', 'Reede', 'Laupäev'];
        $months = ['', 'jaanuar', 'veebruar', 'märts', 'aprill', 'mai', 'juuni', 'juuli', 'august', 'september', 'oktoober', 'november', 'detsember'];

        $ts = strtotime($date);
        $dayName = $days[date('w', $ts)];
        $day = date('j', $ts);
        $month = $months[(int)date('n', $ts)];

        return "{$dayName}, {$day}. {$month}";
    }

    public function clearCache(string $city): void
    {
        Cache::forget("weather_current_{$city}_metric");
        Cache::forget("weather_forecast_{$city}_metric");
    }
}