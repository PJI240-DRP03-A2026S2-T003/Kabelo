<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Profissional extends Model
{
    protected $table = 'profissionais';

    // Apenas os campos válidos restaram aqui
    protected $fillable = ['nome', 'telefone', 'is_ativo'];

    public function servicos(): BelongsToMany
    {
        return $this->belongsToMany(Servico::class, 'profissional_servico');
    }

    public function horariosTrabalho(): HasMany
    {
        return $this->hasMany(HorarioTrabalho::class);
    }
}
