<?php

use App\Http\Controllers\FixtureController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LeagueController;
use App\Http\Controllers\MatchController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\ResultController;
use App\Http\Controllers\TableController;
use App\Http\Controllers\TeamController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/leagues/{slug}', [LeagueController::class, 'show'])->name('leagues.show');
Route::get('/teams/{slug}', [TeamController::class, 'show'])->name('teams.show');
Route::get('/matches/{match}', [MatchController::class, 'show'])->name('matches.show');

Route::get('/fixtures', [FixtureController::class, 'index'])->name('fixtures.index');
Route::get('/fixtures/{slug}', [FixtureController::class, 'show'])->name('fixtures.show');

Route::get('/results', [ResultController::class, 'index'])->name('results.index');
Route::get('/results/{slug}', [ResultController::class, 'show'])->name('results.show');

Route::get('/tables', [TableController::class, 'index'])->name('tables.index');

Route::get('/news', [NewsController::class, 'index'])->name('news.index');
Route::get('/news/article/{slug}', [NewsController::class, 'show'])->name('news.show');
Route::get('/news/{category}', [NewsController::class, 'category'])->name('news.category');
