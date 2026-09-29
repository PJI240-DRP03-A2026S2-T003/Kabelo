<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\CarbonInterface;

class BloqueioAgenda extends Model
{
    protected $table = 'bloqueios_agenda';
    
    protected $fillable = [
        'profissional_id',
        'inicio',
        'fim',
        'motivo',
        'recorrencia',
        'dias_semana',
        'recorrencia_ate',
    ];

    protected function casts(): array
    {
        return [
            'inicio' => 'datetime',
            'fim' => 'datetime',
            'dias_semana' => 'array',
            'recorrencia_ate' => 'date',
        ];
    }


    public function profissional(): BelongsTo
    {
        return $this->belongsTo(Profissional::class);
    }


    /**
     * Devolve [inicio, fim] do bloqueio no dia informado, ou null se não vale nesse dia.
     */
    public function ocorrenciaEm(CarbonInterface $dia): ?array
    {
        $dia = $dia->copy()->startOfDay();

        if (! $this->recorrencia || $this->recorrencia === 'nenhuma') {
            return $this->inicio < $dia->copy()->endOfDay() && $this->fim > $dia
                ? [$this->inicio, $this->fim]
                : null;
        }

        if ($dia < $this->inicio->copy()->startOfDay()) {
            return null;
        }

        if ($this->recorrencia_ate && $dia > $this->recorrencia_ate->copy()->startOfDay()) {
            return null;
        }

        $ocorre = match ($this->recorrencia) {
            'diaria' => true,
            'semanal' => in_array($dia->dayOfWeek, $this->dias_semana ?: [$this->inicio->dayOfWeek]),
            'mensal' => $dia->day === $this->inicio->day,
            default => false,
        };

        if (! $ocorre) {
            return null;
        }

        return [
            $dia->copy()->setTimeFrom($this->inicio),
            $dia->copy()->setTimeFrom($this->fim),
        ];
    }
}