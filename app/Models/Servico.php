<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Servico extends Model
{
    protected $fillable = [
        'nome',
        'descricao',
        'preco',
        'duracao_minutos',
        'is_ativo',
    ];

    public function profissionais(): BelongsToMany
    {
        return $this->belongsToMany(Profissional::class, 'profissional_servico');
    }

}
