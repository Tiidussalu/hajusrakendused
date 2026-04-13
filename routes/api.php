<?php

use App\Http\Controllers\FilmController;
use App\Http\Controllers\MapController;
use App\Http\Controllers\WeatherController;
use App\Http\Controllers\ExternalApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| JSON API Routes
|--------------------------------------------------------------------------
|
| GET  /api/v1/films          - Filmide nimekiri (search, genre, sort, limit)
| GET  /api/v1/films/{film}   - Üksiku filmi andmed
| GET  /api/v1/markers        - Kõik kaardile lisatud markerid
| GET  /api/v1/weather        - Ilmaandmed linna järgi (?city=Tallinn)
|
| Autentimine: lisa päisele  Authorization: Bearer <sinu-api-võti>
| või query parameetrile:    ?api_token=<sinu-api-võti>
|
*/

Route::prefix('v1')->group(function () {

    // Filmide API
    Route::get('/films',        [FilmController::class, 'apiIndex'])->name('api.films.index');
    Route::get('/films/{film}', [FilmController::class, 'apiShow'])->name('api.films.show');

    // Markerite API
    Route::get('/markers',      [MapController::class, 'apiMarkers'])->name('api.markers.index');

    // Ilmaandmete API
    Route::get('/weather',      [WeatherController::class, 'apiWeather'])->name('api.weather');

    // Välise API proxy
    Route::get('/external/sharks', [ExternalApiController::class, 'getSharks'])->name('api.external.sharks');
});

// Backwards-compatible aliases
Route::get('/films',        [FilmController::class, 'apiIndex']);
Route::get('/films/{film}', [FilmController::class, 'apiShow']);