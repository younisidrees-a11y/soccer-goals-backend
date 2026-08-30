<?php

use App\Http\Controllers\FixtureController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LeagueController;
use App\Http\Controllers\MatchController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\PlayerController;
use App\Http\Controllers\ResultController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\TableController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\TodayController;
use Illuminate\Support\Facades\Route;

Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap.index');
Route::get('/sitemap-news.xml', [SitemapController::class, 'news'])->name('sitemap.news');
Route::get('/sitemap-matches.xml', [SitemapController::class, 'matches'])->name('sitemap.matches');
Route::get('/sitemap-players.xml', [SitemapController::class, 'players'])->name('sitemap.players');

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::view('/about', 'about')->name('about');
Route::get('/leagues', [LeagueController::class, 'index'])->name('leagues.index');
Route::get('/leagues/{slug}', [LeagueController::class, 'show'])->name('leagues.show');
Route::get('/teams/{slug}', [TeamController::class, 'show'])->name('teams.show');
Route::get('/players/{player}/{slug?}', [PlayerController::class, 'show'])->name('players.show');
Route::get('/matches/{match}/{month?}/{slug?}', [MatchController::class, 'show'])->name('matches.show');

Route::get('/today', [TodayController::class, 'index'])->name('today.index');

Route::get('/fixtures', [FixtureController::class, 'index'])->name('fixtures.index');
Route::get('/fixtures/{slug}', [FixtureController::class, 'show'])->name('fixtures.show');

Route::get('/results', [ResultController::class, 'index'])->name('results.index');
Route::get('/results/{slug}', [ResultController::class, 'show'])->name('results.show');

Route::get('/tables', [TableController::class, 'index'])->name('tables.index');

Route::get('/news', [NewsController::class, 'index'])->name('news.index');
Route::get('/news/article/{slug}', [NewsController::class, 'show'])->name('news.show');
Route::get('/news/{category}', [NewsController::class, 'category'])->name('news.category');
