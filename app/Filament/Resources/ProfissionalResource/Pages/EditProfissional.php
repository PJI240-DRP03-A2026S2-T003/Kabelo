<?php

namespace App\Filament\Resources\ProfissionalResource\Pages;

use App\Filament\Resources\ProfissionalResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProfissional extends EditRecord
{
    protected static string $resource = ProfissionalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
