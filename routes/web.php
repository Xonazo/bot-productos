<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductWebController;

Route::get('/', function () {
    return view('fondas');
});




Route::get('/productos/crear', [ProductWebController::class, 'create'])->name('products.create');
Route::post('/productos', [ProductWebController::class, 'store'])->name('products.store');


Route::get('/fondas', function () {
    return view('fondas');
});