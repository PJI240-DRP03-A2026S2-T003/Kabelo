<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            if (! Schema::hasColumn('clientes', 'telefone')) {
                $table->string('telefone', 20)->nullable();
            }

            if (! Schema::hasColumn('clientes', 'aceita_whatsapp')) {
                $table->boolean('aceita_whatsapp')->default(false);
            }
        });

        Schema::table('agendamentos', function (Blueprint $table) {
            if (! Schema::hasColumn('agendamentos', 'lembrete_enviado_em')) {
                $table->timestamp('lembrete_enviado_em')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            if (Schema::hasColumn('clientes', 'aceita_whatsapp')) {
                $table->dropColumn('aceita_whatsapp');
            }
        });

        Schema::table('agendamentos', function (Blueprint $table) {
            if (Schema::hasColumn('agendamentos', 'lembrete_enviado_em')) {
                $table->dropColumn('lembrete_enviado_em');
            }
        });
    }
};