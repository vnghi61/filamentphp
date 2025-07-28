<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('filament.admin.pages.dashboard');
});

Route::middleware('web')->group(function () {
    Route::get('/set-locale/{locale}', function ($locale) {
        session(['locale' => $locale]);
        
        return redirect()->back(); // Quay lại trang trước đó
    })->name('set-locale');
});

