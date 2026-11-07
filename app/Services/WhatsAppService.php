<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class WhatsAppService
{
    public function enviarTemplate(string $telefone, string $template, array $parametros): array
    {
        $cfg = config('services.whatsapp');

        return Http::withToken($cfg['token'])
            ->post("https://graph.facebook.com/{$cfg['version']}/{$cfg['phone_number_id']}/messages", [
                'messaging_product' => 'whatsapp',
                'to' => $telefone, // formato internacional, só números: 5512999999999
                'type' => 'template',
                'template' => [
                    'name' => $template,
                    'language' => ['code' => 'pt_BR'],
                    'components' => [[
                        'type' => 'body',
                        'parameters' => array_map(
                            fn ($p) => ['type' => 'text', 'text' => (string) $p],
                            $parametros
                        ),
                    ]],
                ],
            ])
            ->throw()
            ->json();
    }
}