<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/whatsapp/webhook', function (Request $r) {
    return $r->query('hub_verify_token') === config('services.whatsapp.verify_token')
        ? response($r->query('hub_challenge'))
        : response('Token inválido', 403);
});

Route::post('/whatsapp/webhook', function (Request $r) {
    // $r->all() traz status de entrega e mensagens recebidas
    return response()->noContent();
});