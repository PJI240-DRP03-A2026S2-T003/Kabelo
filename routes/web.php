<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ClienteController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/health', fn () => response()->json(['status' => 'ok']));

// Chamada pelo Vercel Cron: executa as tarefas agendadas (routes/console.php)
Route::get('/cron/schedule', function (Request $request) {
    $segredo = env('CRON_SECRET');

    abort_unless(
        $segredo && hash_equals("Bearer {$segredo}", (string) $request->header('Authorization')),
        401
    );

    Artisan::call('schedule:run');

    return response()->json(['ok' => true]);
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