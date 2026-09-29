<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AgendamentoResource\Pages;
use App\Filament\Resources\AgendamentoResource\RelationManagers;
use App\Models\Agendamento;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AgendamentoResource extends Resource
{
    protected static ?string $model = Agendamento::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Detalhes do Agendamento')
                    ->schema([
                        // Seleção do Cliente
                        Forms\Components\Select::make('cliente_id')
                            ->relationship('cliente', 'nome')
                            ->searchable()
                            ->preload()
                            ->required(),

                        // Seleção do Serviço
                        Forms\Components\Select::make('servico_id')
                            ->relationship('servico', 'nome')
                            ->preload()
                            ->required(),

                        // Seleção do Profissional
                        Forms\Components\Select::make('profissional_id')
                            ->relationship('profissional', 'nome')
                            ->preload()
                            ->required(),

                        // Data e Hora do Atendimento
                        Forms\Components\DateTimePicker::make('data_hora')
                            ->label('Data e Hora')
                            ->required()
                            ->displayFormat('d/m/Y H:i')
                            ->minutesStep(15), // Pula de 15 em 15 minutos ao escolher o horário

                        // Status do Agendamento
                        Forms\Components\Select::make('status')
                            ->options([
                                'pendente' => 'Pendente',
                                'confirmado' => 'Confirmado',
                                'concluido' => 'Concluído',
                                'cancelado' => 'Cancelado',
                            ])
                            ->default('pendente')
                            ->required(),

                        // Observações
                        Forms\Components\Textarea::make('observacoes')
                            ->columnSpanFull()
                            ->placeholder('Ex: Cliente alérgica a marcas específicas ou prefere atendimento rápido.'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('cliente.nome')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('profissional.nome')
                    ->label('Profissional')
                    ->sortable(),

                Tables\Columns\TextColumn::make('servico.nome')
                    ->label('Serviço'),

                Tables\Columns\TextColumn::make('data_hora')
                    ->label('Data / Hora')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->badge() // Transforma o status em uma etiqueta colorida elegante
                    ->color(fn (string $state): string => match ($state) {
                        'pendente' => 'warning',
                        'confirmado' => 'info',
                        'concluido' => 'success',
                        'cancelado' => 'danger',
                    }),
            ])
            ->filters([
                // Filtros rápidos por status podem ser adicionados aqui
            ]);
    }


    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAgendamentos::route('/'),
            'create' => Pages\CreateAgendamento::route('/create'),
            'edit' => Pages\EditAgendamento::route('/{record}/edit'),
        ];
    }
}
