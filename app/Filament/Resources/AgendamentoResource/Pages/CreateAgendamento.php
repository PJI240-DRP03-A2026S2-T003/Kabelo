<?php

namespace App\Filament\Resources\AgendamentoResource\Pages;

use App\Filament\Resources\AgendamentoResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateAgendamento extends CreateRecord
{
    protected static string $resource = AgendamentoResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return AgendamentoResource::definirHorario($data, $this->data);
    }
}
