<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/admin/login');

Route::get('/health', fn () => response()->json(['status' => 'ok']));

// Chamada pelo Vercel Cron: envia os lembretes de WhatsApp dos agendamentos de amanhã
Route::get('/cron/lembretes', function (Request $request) {
    $segredo = env('CRON_SECRET');

    abort_unless(
        $segredo && hash_equals("Bearer {$segredo}", (string) $request->header('Authorization')),
        401
    );

    $codigo = Artisan::call('agendamentos:lembretes');

    return response()->json(['ok' => $codigo === 0]);
});
