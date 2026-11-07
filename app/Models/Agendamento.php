<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Agendamento extends Model
{
    protected $fillable = [
        'cliente_id',
        'profissional_id',
        'servico_id',
        'data_hora',
        'status',
        'observacoes',
        'data_hora_fim',
        'lembrete_enviado_em',
    ];

    protected static function booted(): void
    {
        static::updating(function (Agendamento $agendamento) {
            if ($agendamento->isDirty('data_hora') && ! $agendamento->isDirty('lembrete_enviado_em')) {
                $agendamento->lembrete_enviado_em = null;
            }
        });
    }

    // Converte os campos de data automaticamente em objetos Carbon (Data do PHP)
    protected function casts(): array
    {
        return [
            'data_hora' => 'datetime',
            'data_hora_fim' => 'datetime',
            'lembrete_enviado_em' => 'datetime',
        ];
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function profissional(): BelongsTo
    {
        return $this->belongsTo(Profissional::class);
    }

    public function servico(): BelongsTo
    {
        return $this->belongsTo(Servico::class);
    }
}