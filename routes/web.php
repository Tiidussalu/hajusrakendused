<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\FilmController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MapController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\WeatherController;
use App\Http\Controllers\ApiTokenController;
use Illuminate\Support\Facades\Route;

// ── Home ──────────────────────────────────────────────────────
Route::get('/', [HomeController::class, 'index'])->name('home');

// ── Auth ──────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login',    [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login',   [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register',[AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// ── 1. Ilm ────────────────────────────────────────────────────
Route::get('/ilm',        [WeatherController::class, 'index'])->name('weather.index');
Route::post('/ilm/otsi',  [WeatherController::class, 'search'])->name('weather.search');

// ── 2. Kaart ──────────────────────────────────────────────────
Route::get('/kaart', [MapController::class, 'index'])->name('map.index');

Route::prefix('map/markers')->name('map.markers.')->group(function () {
    Route::post('/',          [MapController::class, 'store'])->name('store');
    Route::put('/{marker}',   [MapController::class, 'update'])->name('update');
    Route::delete('/{marker}',[MapController::class, 'destroy'])->name('destroy');
});

// ── 3. Blogi ──────────────────────────────────────────────────
Route::get('/blogi', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blogi/{post:slug}', [BlogController::class, 'show'])->name('blog.show');
Route::post('/blogi/{post:slug}/kommentaar', [BlogController::class, 'storeComment'])->name('blog.comment.store');

Route::middleware('auth')->group(function () {
    Route::get('/blogi/uus/postitus',      [BlogController::class, 'create'])->name('blog.create');
    Route::post('/blogi',                  [BlogController::class, 'store'])->name('blog.store');
    Route::get('/blogi/{post:slug}/muuda', [BlogController::class, 'edit'])->name('blog.edit');
    Route::put('/blogi/{post:slug}',       [BlogController::class, 'update'])->name('blog.update');
    Route::delete('/kommentaar/{comment}', [BlogController::class, 'destroyComment'])->name('blog.comment.destroy');
});

// ── 4. Pood ───────────────────────────────────────────────────
Route::get('/pood',                [ShopController::class, 'index'])->name('shop.index');
Route::get('/pood/kassa',          [ShopController::class, 'checkout'])->name('shop.checkout');
Route::post('/pood/payment-intent',[ShopController::class, 'createPaymentIntent'])->name('shop.payment-intent');
Route::get('/pood/edu',            [ShopController::class, 'paymentSuccess'])->name('shop.success');

Route::post('/stripe/webhook', [ShopController::class, 'stripeWebhook'])
    ->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class)
    ->name('stripe.webhook');

// ── 5. Filmid (API explorer) ──────────────────────────────────
Route::get('/filmid',      [FilmController::class, 'index'])->name('api-explorer.index');
Route::get('/filmid/lisa', [FilmController::class, 'create'])->name('api-explorer.create');

Route::middleware('auth')->group(function () {
    Route::post('/filmid',          [FilmController::class, 'store'])->name('api-explorer.store');
    Route::delete('/filmid/{film}', [FilmController::class, 'destroy'])->name('api-explorer.destroy');
});

// ── 6. API Token haldus ───────────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::get('/api-token',          [ApiTokenController::class, 'show'])->name('api.token');
    Route::post('/api-token/generate',[ApiTokenController::class, 'generate'])->name('api.token.generate');
    Route::post('/api-token/revoke',  [ApiTokenController::class, 'revoke'])->name('api.token.revoke');
});