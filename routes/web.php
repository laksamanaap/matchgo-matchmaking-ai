<?php

use Illuminate\Support\Facades\Route;

// ─── Landing Page ────────────────────────────────────────────────────────────
Route::get('/', function () {
    return view('welcome');
})->name('home');

// ─── Static / Info Pages ─────────────────────────────────────────────────────
Route::get('/tentang', function () {
    return view('pages.tentang');
})->name('tentang');

Route::get('/cara-kerja', function () {
    return view('pages.cara-kerja');
})->name('cara-kerja');

Route::get('/kontak', function () {
    return view('pages.kontak');
})->name('kontak');

// ─── Auth Routes (uncomment when you add authentication) ─────────────────────
// Route::get('/login',    [App\Http\Controllers\AuthController::class, 'showLogin'])->name('login');
// Route::post('/login',   [App\Http\Controllers\AuthController::class, 'login']);
// Route::get('/register', [App\Http\Controllers\AuthController::class, 'showRegister'])->name('register');
// Route::post('/register',[App\Http\Controllers\AuthController::class, 'register']);
// Route::post('/logout',  [App\Http\Controllers\AuthController::class, 'logout'])->name('logout');

// ─── Protected Routes (uncomment when auth is ready) ─────────────────────────
// Route::middleware('auth')->group(function () {
//
//     // Dashboard
//     Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
//
//     // Team Management
//     Route::resource('tim', App\Http\Controllers\TeamController::class);
//
//     // Match / Matchmaking
//     Route::get('/cari-lawan',       [App\Http\Controllers\MatchController::class, 'index'])->name('match.index');
//     Route::post('/cari-lawan',      [App\Http\Controllers\MatchController::class, 'search'])->name('match.search');
//     Route::post('/match/{id}/join', [App\Http\Controllers\MatchController::class, 'join'])->name('match.join');
//     Route::get('/match/{id}',       [App\Http\Controllers\MatchController::class, 'show'])->name('match.show');
//
//     // Venue / Lapangan
//     Route::get('/lapangan',      [App\Http\Controllers\VenueController::class, 'index'])->name('venue.index');
//     Route::get('/lapangan/{id}', [App\Http\Controllers\VenueController::class, 'show'])->name('venue.show');
//
//     // Booking & Payment
//     Route::post('/booking',        [App\Http\Controllers\BookingController::class, 'store'])->name('booking.store');
//     Route::get('/booking/{id}',    [App\Http\Controllers\BookingController::class, 'show'])->name('booking.show');
//     Route::get('/riwayat-booking', [App\Http\Controllers\BookingController::class, 'history'])->name('booking.history');
//
//     // Profile
//     Route::get('/profil',  [App\Http\Controllers\ProfileController::class, 'show'])->name('profile.show');
//     Route::put('/profil',  [App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
//
// });