<?php

use App\Http\Controllers\LocaleController;
use App\Livewire\Demo\UiKitchenSink;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::view('/dashboard', 'dashboard.index')->name('dashboard');

Route::get('/ui/kitchen-sink', UiKitchenSink::class)->name('ui.kitchen-sink');

Route::get('/locale/{locale}', [LocaleController::class, 'update'])->name('locale.update');
