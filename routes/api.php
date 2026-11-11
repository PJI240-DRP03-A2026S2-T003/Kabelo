<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Verificação do webhook: a Meta chama este endereço uma vez ao cadastrar a URL
Route::get('/whatsapp/webhook', function (Request $request) {
    $token = config('services.whatsapp.verify_token');

    if ($request->query('hub_mode') === 'subscribe'
        && $token
        && hash_equals($token, (string) $request->query('hub_verify_token'))) {
        return response((string) $request->query('hub_challenge'), 200)
            ->header('Content-Type', 'text/plain');
    }

    return response('Token inválido', 403);
});

// Eventos: status de entrega e mensagens recebidas
Route::post('/whatsapp/webhook', function (Request $request) {
    Log::info('whatsapp.webhook', $request->all());

    return response()->noContent();
});