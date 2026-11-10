<?php

use App\Http\Controllers\CarController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\ShowroomPageController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::livewire('/cars', 'pages::showroom.inventory')->name('cars.index');
Route::get('/cars/{car}', [CarController::class, 'show'])->name('cars.show');
Route::livewire('/brands/{brand}', 'pages::showroom.inventory')->name('brands.show');
Route::livewire('/sell-your-car', 'pages::showroom.sell')->name('sell');
Route::get('/about', [ShowroomPageController::class, 'about'])->name('about');
Route::livewire('/contact', 'pages::showroom.contact')->name('contact');
Route::livewire('/compare', 'pages::showroom.compare')->name('compare');
Route::livewire('/test-drive', 'pages::showroom.test-drive')->name('test-drive');
Route::livewire('/saved', 'pages::showroom.wishlist')->name('wishlist');
Route::get('/loan/{car?}', LoanController::class)->name('loan');
