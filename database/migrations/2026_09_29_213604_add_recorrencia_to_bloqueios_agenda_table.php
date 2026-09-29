<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bloqueios_agenda', function (Blueprint $table) {
            $table->string('recorrencia')->default('nenhuma')->after('fim');
            $table->json('dias_semana')->nullable()->after('recorrencia');
            $table->date('recorrencia_ate')->nullable()->after('dias_semana');
        });
    }

    public function down(): void
    {
        Schema::table('bloqueios_agenda', function (Blueprint $table) {
            $table->dropColumn(['recorrencia', 'dias_semana', 'recorrencia_ate']);
        });
    }
};
