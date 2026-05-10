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

    Route::get('/pertandingan',                    [MatchController::class, 'index'])->name('match.index');
    Route::get('/pertandingan/buat',               [MatchController::class, 'create'])->name('match.create');
    Route::post('/pertandingan/buat',              [MatchController::class, 'store'])->name('match.store');
    Route::get('/pertandingan/{id}',               [MatchController::class, 'show'])->name('match.show');
    Route::post('/pertandingan/{id}/terima',       [MatchController::class, 'accept'])->name('match.accept');
    Route::post('/pertandingan/{id}/tolak',        [MatchController::class, 'reject'])->name('match.reject');

    Route::get('/automatching',                    [AutomatchController::class, 'index'])->name('automatch.index');
    Route::post('/automatching/jalankan',          [AutomatchController::class, 'run'])->name('automatch.run');
    Route::post('/automatching/tantang',           [AutomatchController::class, 'challenge'])->name('automatch.challenge');

    Route::post('/automatching/queue/join',        [MatchmakingController::class, 'join'])->name('automatch.queue.join');
    Route::post('/automatching/queue/leave',       [MatchmakingController::class, 'leave'])->name('automatch.queue.leave');
    Route::post('/automatching/match/{id}/accept', [MatchmakingController::class, 'accept'])->name('automatch.match.accept');
    Route::post('/automatching/match/{id}/reject', [MatchmakingController::class, 'reject'])->name('automatch.match.reject');

    Route::get('/cari-lawan',                      [DiscoverController::class, 'index'])->name('discover.index');
    Route::get('/cari-lawan/{id}',                 [DiscoverController::class, 'show'])->name('discover.show');

    Route::get('/lapangan',                        [VenueController::class, 'index'])->name('venue.index');
    Route::get('/lapangan/{id}',                   [VenueController::class, 'show'])->name('venue.show');
    Route::post('/lapangan/{id}/pesan',            [VenueController::class, 'book'])->name('venue.book');
});
