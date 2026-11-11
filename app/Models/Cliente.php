<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    protected $fillable = [
        'nome',
        'email',
        'telefone',
        'aceita_whatsapp',
    ];

    protected function casts(): array
    {
        return [
            'aceita_whatsapp' => 'boolean',
        ];
    }
}
