<?php

namespace App\Filament\Widgets;

use App\Models\Agendamento;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class ProximosAgendamentos extends TableWidget
{
    protected static ?string $heading = 'Próximos atendimentos';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Agendamento::query()
                    ->whereIn('status', ['pendente', 'confirmado'])
                    ->where('data_hora', '>=', now())
                    ->orderBy('data_hora')
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('data_hora')
                    ->label('Quando')
                    ->dateTime('d/m H:i'),
                Tables\Columns\TextColumn::make('cliente.nome')->label('Cliente'),
                Tables\Columns\TextColumn::make('servico.nome')->label('Serviço'),
                Tables\Columns\TextColumn::make('profissional.nome')->label('Profissional'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pendente' => 'warning',
                        'confirmado' => 'info',
                        default => 'gray',
                    }),
            ])
            ->paginated(false);
    }
}