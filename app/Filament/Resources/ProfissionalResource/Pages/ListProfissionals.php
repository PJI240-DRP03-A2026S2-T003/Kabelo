<?php

namespace App\Filament\Resources\ProfissionalResource\Pages;

use App\Filament\Resources\ProfissionalResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProfissionals extends ListRecords
{
    protected static string $resource = ProfissionalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
