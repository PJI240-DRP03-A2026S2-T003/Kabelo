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
    ];

    // Converte o campo data_hora automaticamente em um objeto Carbon (Data do PHP)
    protected function casts(): array
    {
        return [
            'data_hora' => 'datetime',
            'data_hora_fim' => 'datetime',
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
