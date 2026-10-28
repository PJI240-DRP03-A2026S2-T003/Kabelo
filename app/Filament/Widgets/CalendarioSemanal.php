<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\AgendamentoResource;
use App\Models\Agendamento;
use App\Models\BloqueioAgenda;
use App\Models\Profissional;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Filament\Widgets\Widget;

class CalendarioSemanal extends Widget
{
    protected static string $view = 'filament.widgets.calendario-semanal';

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    public ?int $profissionalId = null;

    public function getProfissionais(): array
    {
        return Profissional::orderBy('nome')->pluck('nome', 'id')->all();
    }

    /**
     * Chamado pelo FullCalendar sempre que troca de semana/visão.
     */
    public function buscarEventos(string $inicio, string $fim): array
    {
        // Remove o fuso da string: os horários do sistema são guardados como horário local
        $inicio = Carbon::parse(substr($inicio, 0, 19));
        $fim = Carbon::parse(substr($fim, 0, 19));

        $cores = [
            'pendente' => '#f59e0b',
            'confirmado' => '#3b82f6',
            'concluido' => '#22c55e',
        ];

        $eventos = Agendamento::with(['cliente', 'servico', 'profissional'])
            ->where('status', '!=', 'cancelado')
            ->when($this->profissionalId, fn ($q) => $q->where('profissional_id', $this->profissionalId))
            ->where('data_hora', '<', $fim)
            ->where('data_hora_fim', '>', $inicio)
            ->get()
            ->map(fn (Agendamento $a) => [
                'id' => (string) $a->id,
                'title' => $a->cliente->nome . ' · ' . $a->servico->nome,
                'start' => $a->data_hora->format('Y-m-d\TH:i:s'),
                'end' => $a->data_hora_fim->format('Y-m-d\TH:i:s'),
                'color' => $cores[$a->status] ?? '#6b7280',
                'url' => AgendamentoResource::getUrl('edit', ['record' => $a]),
                'extendedProps' => ['profissional' => $a->profissional->nome],
            ])
            ->all();

        $bloqueios = BloqueioAgenda::query()
            ->when($this->profissionalId, fn ($q) => $q->where(fn ($q) => $q
                ->whereNull('profissional_id')
                ->orWhere('profissional_id', $this->profissionalId)))
            ->where(fn ($q) => $q
                ->where(fn ($q) => $q
                    ->where('recorrencia', 'nenhuma')
                    ->where('inicio', '<', $fim)
                    ->where('fim', '>', $inicio))
                ->orWhere(fn ($q) => $q
                    ->where('recorrencia', '!=', 'nenhuma')
                    ->where('inicio', '<=', $fim)
                    ->where(fn ($q) => $q
                        ->whereNull('recorrencia_ate')
                        ->orWhere('recorrencia_ate', '>=', $inicio->toDateString()))))
            ->get();

        $faixas = [];

        foreach ($bloqueios as $b) {
            foreach (CarbonPeriod::create($inicio->copy()->startOfDay(), $fim->copy()->subSecond()->startOfDay()) as $dia) {
                if ($o = $b->ocorrenciaEm($dia)) {
                    $start = $o[0]->format('Y-m-d\TH:i:s');

                    $faixas["{$b->id}-{$start}"] = [
                        'title' => $b->motivo ?: 'Bloqueado',
                        'start' => $start,
                        'end' => $o[1]->format('Y-m-d\TH:i:s'),
                        'display' => 'background',
                        'color' => '#9ca3af',
                    ];
                }
            }
        }

        return array_merge($eventos, array_values($faixas));
    }
}