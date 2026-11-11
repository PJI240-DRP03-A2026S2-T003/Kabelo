<?php

namespace App\Console\Commands;

use App\Models\Agendamento;
use App\Services\WhatsAppService;
use Illuminate\Console\Command;
use Illuminate\Http\Client\RequestException;

class EnviarLembretesWhatsApp extends Command
{
    protected $signature = 'agendamentos:lembretes {--simular : Só lista o que seria enviado, sem enviar nada}';

    protected $description = 'Envia por WhatsApp o lembrete dos agendamentos de amanhã';

    public function handle(WhatsAppService $whatsapp): int
    {
        $amanha = now()->addDay();

        $agendamentos = Agendamento::query()
            ->with(['cliente', 'servico', 'profissional'])
            ->whereIn('status', ['pendente', 'confirmado'])
            ->whereNull('lembrete_enviado_em')
            ->whereBetween('data_hora', [$amanha->copy()->startOfDay(), $amanha->copy()->endOfDay()])
            ->orderBy('data_hora')
            ->get();

        if ($agendamentos->isEmpty()) {
            $this->info('Nenhum lembrete pendente para amanhã.');

            return self::SUCCESS;
        }

        $enviados = 0;
        $ignorados = 0;
        $falhas = 0;

        foreach ($agendamentos as $agendamento) {
            $cliente = $agendamento->cliente;

            if (! $cliente?->aceita_whatsapp || blank($cliente->telefone)) {
                $ignorados++;
                $this->line("Ignorado #{$agendamento->id}: cliente sem telefone ou sem aceite de WhatsApp.");

                continue;
            }

            $parametros = [
                $cliente->nome,
                $agendamento->servico->nome,
                $agendamento->profissional->nome,
                $agendamento->data_hora->format('d/m'),
                $agendamento->data_hora->format('H:i'),
            ];

            if ($this->option('simular')) {
                $this->line("Simulado #{$agendamento->id}: {$cliente->telefone} → " . implode(' | ', $parametros));

                continue;
            }

            try {
                $whatsapp->enviarTemplate(
                    $cliente->telefone,
                    config('services.whatsapp.template_lembrete'),
                    $parametros,
                );

                $agendamento->update(['lembrete_enviado_em' => now()]);
                $enviados++;
            } catch (RequestException $e) {
                $falhas++;
                $this->error("Falha #{$agendamento->id}: " . ($e->response->json('error.message') ?? $e->getMessage()));
            }
        }

        $this->info("Enviados: {$enviados} | Ignorados: {$ignorados} | Falhas: {$falhas}");

        return $falhas > 0 ? self::FAILURE : self::SUCCESS;
    }
}