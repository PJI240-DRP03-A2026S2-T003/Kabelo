<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProfissionalResource\Pages;
use App\Filament\Resources\ProfissionalResource\RelationManagers;
use App\Models\Profissional;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProfissionalResource extends Resource
{
    protected static ?string $model = Profissional::class;

    protected static ?string $navigationIcon = 'heroicon-o-identification';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informações do Profissional')
                    ->schema([
                        Forms\Components\TextInput::make('nome')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('telefone'),
                        Forms\Components\Toggle::make('is_ativo')
                            ->label('Ativo no Salão')
                            ->default(true),
                    ])->columns(2),

                Forms\Components\Section::make('Serviços Habilitados')
                    ->description('Selecione quais serviços este profissional realiza no salão')
                    ->schema([
                        Forms\Components\CheckboxList::make('servicos')
                            ->relationship('servicos', 'nome')
                            ->columns(2),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nome')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('telefone'),
                Tables\Columns\IconColumn::make('is_ativo')->label('Status')->boolean(),
            ])
            ->filters([]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\HorariosTrabalhoRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProfissionals::route('/'),
            'create' => Pages\CreateProfissional::route('/create'),
            'edit' => Pages\EditProfissional::route('/{record}/edit'),
        ];
    }
}
