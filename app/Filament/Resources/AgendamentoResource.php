<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AgendamentoResource\Pages;
use App\Models\Agendamento;
use App\Models\Profissional;
use App\Models\Servico;
use App\Services\DisponibilidadeService;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Exceptions\Halt;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

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
                        Forms\Components\Select::make('cliente_id')
                            ->relationship('cliente', 'nome')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Select::make('servico_id')
                            ->relationship('servico', 'nome')
                            ->preload()
                            ->live()
                            ->afterStateUpdated(fn (Set $set) => $set('hora', null) || $set('profissional_id', null))
                            ->required(),

                        Forms\Components\Select::make('profissional_id')
                            ->relationship(
                                'profissional',
                                'nome',
                                modifyQueryUsing: fn (Builder $query, Get $get) => $get('servico_id')
                                    ? $query->whereHas('servicos', fn ($q) => $q->where('servicos.id', $get('servico_id')))
                                    : $query,
                            )
                            ->preload()
                            ->live()
                            ->afterStateUpdated(fn (Set $set) => $set('hora', null))
                            ->required(),

                        Forms\Components\DatePicker::make('data')
                            ->label('Data')
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->minDate(fn (string $operation) => $operation === 'create' ? today() : null)
                            ->live()
                            ->afterStateUpdated(fn (Set $set) => $set('hora', null))
                            ->dehydrated(false)
                            ->required(),

                        Forms\Components\Select::make('hora')
                            ->label('Horário')
                            ->placeholder('Escolha serviço, profissional e data')
                            ->options(function (Get $get, ?Model $record): array {
                                if (! $get('servico_id') || ! $get('profissional_id') || ! $get('data')) {
                                    return [];
                                }

                                $data = Carbon::parse($get('data'));

                                $horas = app(DisponibilidadeService::class)->horariosLivres(
                                    Profissional::findOrFail($get('profissional_id')),
                                    $data,
                                    Servico::findOrFail($get('servico_id')),
                                    ignorarAgendamentoId: $record?->id,
                                );

                                // Na edição, mantém o horário atual da própria marcação na lista
                                if ($record?->data_hora?->isSameDay($data)) {
                                    $horas[] = $record->data_hora->format('H:i');
                                }

                                $horas = array_values(array_unique($horas));
                                sort($horas);

                                return array_combine($horas, $horas);
                            })
                            ->dehydrated(false)
                            ->required(),

                        Forms\Components\Select::make('status')
                            ->options([
                                'pendente' => 'Pendente',
                                'confirmado' => 'Confirmado',
                                'concluido' => 'Concluído',
                                'cancelado' => 'Cancelado',
                            ])
                            ->default('pendente')
                            ->required(),

                        Forms\Components\Textarea::make('observacoes')
                            ->columnSpanFull()
                            ->placeholder('Ex: Cliente alérgica a marcas específicas ou prefere atendimento rápido.'),
                    ])->columns(2),
            ]);
    }

    /**
     * Monta data_hora e data_hora_fim a partir de data + hora e valida o conflito.
     */
    public static function definirHorario(array $dados, array $estado, ?Agendamento $registro = null): array
    {
        $inicio = Carbon::parse($estado['data'])
            ->startOfDay()
            ->setTimeFromTimeString($estado['hora']);
        $servico = Servico::findOrFail($dados['servico_id']);

        $mudou = ! $registro
            || ! $registro->data_hora->equalTo($inicio)
            || $registro->profissional_id != $dados['profissional_id']
            || $registro->servico_id != $dados['servico_id'];

        // Só revalida se algo do horário mudou (assim dá para concluir/cancelar um agendamento antigo)
        if (! $mudou) {
            return $dados;
        }

        $livres = app(DisponibilidadeService::class)->horariosLivres(
            Profissional::findOrFail($dados['profissional_id']),
            $inicio,
            $servico,
            ignorarAgendamentoId: $registro?->id,
        );

        if (! in_array($inicio->format('H:i'), $livres, true)) {
            Notification::make()
                ->title('Horário indisponível')
                ->body('Esse horário foi ocupado ou está bloqueado. Escolha outro.')
                ->danger()
                ->send();

            throw new Halt();
        }

        $dados['data_hora'] = $inicio;
        $dados['data_hora_fim'] = $inicio->copy()->addMinutes($servico->duracao_minutos);

        return $dados;
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

                Tables\Columns\TextColumn::make('data_hora_fim')
                    ->label('Término')
                    ->dateTime('H:i'),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pendente' => 'warning',
                        'confirmado' => 'info',
                        'concluido' => 'success',
                        'cancelado' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->defaultSort('data_hora')
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options([
                    'pendente' => 'Pendente',
                    'confirmado' => 'Confirmado',
                    'concluido' => 'Concluído',
                    'cancelado' => 'Cancelado',
                ]),
                Tables\Filters\SelectFilter::make('profissional_id')
                    ->label('Profissional')
                    ->relationship('profissional', 'nome'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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