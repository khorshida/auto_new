<?php

use Illuminate\Support\Facades\Route;

Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::livewire('/', 'pages::admin.cars')->name('cars');
    Route::livewire('inquiries', 'pages::admin.inquiries')->name('inquiries');
    Route::livewire('crm', 'pages::admin.crm')->name('crm');
});
