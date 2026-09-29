<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServicoResource\Pages;
use App\Filament\Resources\ServicoResource\RelationManagers;
use App\Models\Servico;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ServicoResource extends Resource
{
    protected static ?string $model = Servico::class;

    protected static ?string $navigationIcon = 'heroicon-o-scissors';

    public static function form(Form $form): Form
    {
    return $form
        ->schema([
            Forms\Components\TextInput::make('nome')
                ->required()
                ->maxLength(255)
                ->placeholder('Ex: Corte Degradê'),

            Forms\Components\TextInput::make('preco')
                ->numeric()
                ->required()
                ->prefix('R$')
                ->placeholder('0,00'),

            Forms\Components\TextInput::make('duracao_minutos')
                ->label('Duração (em minutos)')
                ->numeric()
                ->required()
                ->default(30)
                ->suffix(' min'),

            Forms\Components\Toggle::make('is_ativo')
                ->label('Serviço Ativo')
                ->default(true),

            Forms\Components\Textarea::make('descricao')
                ->columnSpanFull()
                ->placeholder('Descreva detalhes do serviço se necessário...'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nome')->searchable()->sortable(),
                
                Tables\Columns\TextColumn::make('preco')
                    ->money('BRL')
                    ->sortable(),

                Tables\Columns\TextColumn::make('duracao_minutos')
                    ->label('Duração')
                    ->suffix(' min'),

                Tables\Columns\IconColumn::make('is_ativo')
                    ->label('Status')
                    ->boolean(),
            ])
            ->filters([
                // Filtros rápidos se desejar no futuro
            ]);
    }


    public static function getRelations(): array
    {
        return [
            RelationManagers\ProfissionaisRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListServicos::route('/'),
            'create' => Pages\CreateServico::route('/create'),
            'edit' => Pages\EditServico::route('/{record}/edit'),
        ];
    }
}
