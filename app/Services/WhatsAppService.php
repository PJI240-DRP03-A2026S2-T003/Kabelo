<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class WhatsAppService
{
    public function enviarTemplate(
        string $telefone,
        string $template,
        array $parametros = [],
        string $idioma = 'pt_BR',
    ): array {
        $cfg = config('services.whatsapp');

        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $this->normalizarTelefone($telefone),
            'type' => 'template',
            'template' => [
                'name' => $template,
                'language' => ['code' => $idioma],
            ],
        ];

        if ($parametros) {
            $payload['template']['components'] = [[
                'type' => 'body',
                'parameters' => array_map(
                    fn ($p) => ['type' => 'text', 'text' => (string) $p],
                    array_values($parametros)
                ),
            ]];
        }

        return Http::withToken($cfg['token'])
            ->acceptJson()
            ->timeout(15)
            ->post("https://graph.facebook.com/{$cfg['version']}/{$cfg['phone_number_id']}/messages", $payload)
            ->throw()
            ->json();
    }

    /**
     * Deixa o número no formato internacional, só dígitos: 5512999999999.
     */
    public function normalizarTelefone(string $telefone): string
    {
        $numeros = ltrim(preg_replace('/\D+/', '', $telefone), '0');

        if (! str_starts_with($numeros, '55') || strlen($numeros) < 12) {
            $numeros = '55' . $numeros;
        }

        return $numeros;
    }
}