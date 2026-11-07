<?php

namespace App\Filament\Resources\ProfissionalResource\RelationManagers;

use App\Models\HorarioTrabalho;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class HorariosTrabalhoRelationManager extends RelationManager
{
    protected static string $relationship = 'horariosTrabalho';

    protected static ?string $title = 'Horários de trabalho';

    protected static ?string $modelLabel = 'horário';

    protected static ?string $pluralModelLabel = 'horários';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('dia_semana')
                    ->label('Dia da semana')
                    ->options(HorarioTrabalho::DIAS)
                    ->required(),

                Forms\Components\TimePicker::make('hora_inicio')
                    ->label('Início')
                    ->seconds(false)
                    ->minutesStep(15)
                    ->required(),

                Forms\Components\TimePicker::make('hora_fim')
                    ->label('Fim')
                    ->seconds(false)
                    ->minutesStep(15)
                    ->after('hora_inicio')
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('dia_semana')
            ->columns([
                Tables\Columns\TextColumn::make('dia_semana')
                    ->label('Dia')
                    ->formatStateUsing(fn ($state) => HorarioTrabalho::DIAS[$state] ?? $state)
                    ->sortable(),

                Tables\Columns\TextColumn::make('hora_inicio')
                    ->label('Início')
                    ->time('H:i'),

                Tables\Columns\TextColumn::make('hora_fim')
                    ->label('Fim')
                    ->time('H:i'),
            ])
            ->defaultSort('dia_semana')
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
}