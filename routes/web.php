<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ClienteController;
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

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::resource('clientes', ClienteController::class);

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});