<?php

namespace App\Filament\Resources\BloqueioAgendaResource\Pages;

use App\Filament\Resources\BloqueioAgendaResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageBloqueioAgendas extends ManageRecords
{
    protected static string $resource = BloqueioAgendaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
