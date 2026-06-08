<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\AutomatchController;
use App\Http\Controllers\DiscoverController;
use App\Http\Controllers\MatchController;
use App\Http\Controllers\MatchmakingController;
use App\Http\Controllers\VenueController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('welcome'))->name('home');
Route::get('/tentang', fn () => view('pages.tentang'))->name('tentang');
Route::get('/cara-kerja', fn () => view('pages.cara-kerja'))->name('cara-kerja');
Route::get('/kontak', fn () => view('pages.kontak'))->name('kontak');

Route::middleware('guest')->group(function () {
    Route::get('/login',    [LoginController::class, 'show'])->name('login');
    Route::post('/login',   [LoginController::class, 'login']);
    Route::get('/register', [RegisterController::class, 'show'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
});

Route::post('/logout', [LoginController::class, 'logout'])
    ->name('logout')
    ->middleware('auth');

Route::middleware('auth')->group(function () {

use App\Http\Controllers\AuthController;
use App\Http\Controllers\FieldController;
use App\Http\Controllers\MatchController;
use App\Http\Controllers\MatchRequestController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PlayerController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TeamController;

// ─── Auth Routes ─────────────────────────────────────────────────────────────
Route::get('/login', [AuthController::class, 'showLogin'])->name('login.form');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register.form');
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->name('password.email');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');

// ─── Protected Routes ───────────────────────────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::apiResource('teams', TeamController::class)->except(['create', 'edit']);
    Route::post('/players/demo-fill', [PlayerController::class, 'demoFill'])->name('players.demo_fill');
    Route::apiResource('players', PlayerController::class)->except(['create', 'edit']);

    Route::get('/match-requests', [MatchRequestController::class, 'index'])->name('match_requests.index');
    Route::post('/match-requests', [MatchRequestController::class, 'store'])->name('match_requests.store');
    Route::post('/match-requests/{matchRequest}/cancel', [MatchRequestController::class, 'cancel'])->name('match_requests.cancel');

    Route::get('/matchmaking', [MatchController::class, 'index'])->name('matchmaking.index');
    Route::get('/matches', [MatchController::class, 'index'])->name('matches.index');
    Route::get('/matches/create', [MatchController::class, 'create'])->name('matches.create');
    Route::get('/matches/take', [MatchController::class, 'take'])->name('matches.take');
    Route::get('/matches/auto', [MatchController::class, 'auto'])->name('matches.auto');
    Route::get('/matches/auto/status', [MatchController::class, 'autoStatus'])->name('matches.auto_status');
    Route::get('/matches/history', [MatchController::class, 'history'])->name('matches.history');
    Route::post('/matches/auto', [MatchController::class, 'autoStore'])->name('matches.auto_store');
    Route::post('/matches/auto/cancel', [MatchController::class, 'autoCancel'])->name('matches.auto_cancel');
    Route::post('/matches', [MatchController::class, 'store'])->name('matches.store');
    Route::post('/matches/midtrans-token', [MatchController::class, 'midtransToken'])->name('matches.midtrans_token');
    Route::post('/matches/pay-and-create', [MatchController::class, 'payAndCreate'])->name('matches.pay_and_create');
    Route::get('/matches/{match}', [MatchController::class, 'show'])->name('matches.show');
    Route::post('/matches/{match}/auto-confirm', [MatchController::class, 'autoConfirm'])->name('matches.auto_confirm');
    Route::post('/matches/{match}/auto-reject', [MatchController::class, 'autoReject'])->name('matches.auto_reject');
    Route::post('/matches/{match}/accept', [MatchController::class, 'accept'])->name('matches.accept');
    Route::post('/matches/{match}/cancel', [MatchController::class, 'cancel'])->name('matches.cancel');
    Route::post('/match-requests/{matchRequest}/reject', [MatchController::class, 'reject'])->name('match_requests.reject');

    Route::get('/fields', [FieldController::class, 'index'])->name('fields.index');
    Route::get('/fields/{field}', [FieldController::class, 'show'])->name('fields.show');

    Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::post('/payments', [PaymentController::class, 'store'])->name('payments.store');
    Route::post('/payments/midtrans-token', [PaymentController::class, 'midtransToken'])->name('payments.midtrans_token');
    Route::post('/payments/midtrans-finish', [PaymentController::class, 'midtransFinish'])->name('payments.midtrans_finish');
    Route::get('/payments/{payment}', [PaymentController::class, 'show'])->name('payments.show');
    Route::put('/payments/{payment}', [PaymentController::class, 'update'])->name('payments.update');

    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read_all');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
});
