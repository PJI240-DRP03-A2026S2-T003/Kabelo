<?php

namespace App\Services;

use App\Models\Agendamento;
use App\Models\BloqueioAgenda;
use App\Models\Profissional;
use App\Models\Servico;
use Carbon\CarbonInterface;

class DisponibilidadeService
{
    public function horariosLivres(
        Profissional $profissional,
        CarbonInterface $data,
        Servico $servico,
        int $passo = 15,
        ?int $ignorarAgendamentoId = null,
    ): array {
        $dia = $data->copy()->startOfDay();
        $fimDia = $dia->copy()->endOfDay();
        $duracao = $servico->duracao_minutos;

        $janelas = $profissional->horariosTrabalho()
            ->where('dia_semana', $dia->dayOfWeek)
            ->get();

        $agendamentos = Agendamento::where('profissional_id', $profissional->id)
            ->where('status', '!=', 'cancelado')
            ->whereNotNull('data_hora_fim')
            ->when($ignorarAgendamentoId, fn ($q) => $q->where('id', '!=', $ignorarAgendamentoId))
            ->where('data_hora', '<', $fimDia)
            ->where('data_hora_fim', '>', $dia)
            ->get(['data_hora', 'data_hora_fim'])
            ->map(fn ($a) => [$a->data_hora, $a->data_hora_fim]);

        $bloqueios = BloqueioAgenda::where(fn ($q) => $q
            ->whereNull('profissional_id')
            ->orWhere('profissional_id', $profissional->id))
        ->where(fn ($q) => $q
            ->where(fn ($q) => $q
                ->where('recorrencia', 'nenhuma')
                ->where('inicio', '<', $fimDia)
                ->where('fim', '>', $dia))
            ->orWhere(fn ($q) => $q
                ->where('recorrencia', '!=', 'nenhuma')
                ->where('inicio', '<=', $fimDia)
                ->where(fn ($q) => $q
                    ->whereNull('recorrencia_ate')
                    ->orWhere('recorrencia_ate', '>=', $dia->toDateString()))))
        ->get()
        ->map(fn ($b) => $b->ocorrenciaEm($dia))
        ->filter();
        $ocupados = $agendamentos->concat($bloqueios);
        $livres = [];

        foreach ($janelas as $janela) {
            $t = $dia->copy()->setTimeFromTimeString($janela->hora_inicio);
            $fimJanela = $dia->copy()->setTimeFromTimeString($janela->hora_fim);

            while ($t->copy()->addMinutes($duracao)->lte($fimJanela)) {
                $fim = $t->copy()->addMinutes($duracao);

                $conflito = $ocupados->contains(
                    fn ($o) => $o[0] < $fim && $o[1] > $t
                );

                if (! $conflito && $t->isFuture()) {
                    $livres[] = $t->format('H:i');
                }

                $t->addMinutes($passo);
            }
        }

        return $livres;
    }
}