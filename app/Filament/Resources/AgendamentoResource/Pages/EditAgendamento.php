<?php

namespace App\Filament\Resources\AgendamentoResource\Pages;

use App\Filament\Resources\AgendamentoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Carbon;

class EditAgendamento extends EditRecord
{
    protected static string $resource = AgendamentoResource::class;


    protected function mutateFormDataBeforeFill(array $data): array
    {
        $inicio = Carbon::parse($data['data_hora']);
        $data['data'] = $inicio->toDateString();
        $data['hora'] = $inicio->format('H:i');

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return AgendamentoResource::definirHorario($data, $this->data, $this->record);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
