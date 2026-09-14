<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\TelegramController;
use App\Services\FondaService;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');



Route::post('/telegram/webhook', [TelegramController::class, 'handle']);

Route::get('/fondas/nearby', function (Request $request, FondaService $fondaService) {
    $lat = (float) $request->query('lat');
    $lng = (float) $request->query('lng');
    $radiusKm = (float) $request->query('radius', 50);
    $radiusMeters = (int) ($radiusKm * 1000);

    $fondas = $fondaService->searchNearby($lat, $lng, $radiusMeters);

    return response()->json($fondas);
});

Route::get('/fondas/all', function (FondaService $fondaService) {
    return response()->json($fondaService->all());
});