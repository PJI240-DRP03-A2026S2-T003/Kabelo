<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BloqueioAgendaResource\Pages;
use App\Models\BloqueioAgenda;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Carbon\Carbon;
use Filament\Forms\Get;
use Filament\Tables\Table;

class BloqueioAgendaResource extends Resource
{
    protected static ?string $model = BloqueioAgenda::class;

    protected static ?string $navigationIcon = 'heroicon-o-no-symbol';

    protected static ?string $modelLabel = 'Bloqueio de agenda';

    protected static ?string $pluralModelLabel = 'Bloqueios de agenda';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('profissional_id')
                    ->label('Profissional')
                    ->relationship('profissional', 'nome')
                    ->searchable()
                    ->preload()
                    ->placeholder('Todos (salão inteiro)')
                    ->helperText('Deixe em branco para bloquear o salão inteiro, como em um feriado.'),

                Forms\Components\DateTimePicker::make('inicio')
                    ->label('Início')
                    ->seconds(false)
                    ->minutesStep(15)
                    ->displayFormat('d/m/Y H:i')
                    ->required(),

                Forms\Components\DateTimePicker::make('fim')
                // ...o que já existe
                ->rule(fn (Get $get) => function (string $attribute, $value, \Closure $fail) use ($get) {
                    if (($get('recorrencia') ?? 'nenhuma') !== 'nenhuma'
                        && ! Carbon::parse($value)->isSameDay(Carbon::parse($get('inicio')))) {
                        $fail('Em bloqueios com repetição, início e fim devem ser no mesmo dia.');
                    }
                }),

                Forms\Components\TextInput::make('motivo')
                    ->placeholder('Ex: Férias, feriado, consulta médica')
                    ->maxLength(255),
                Forms\Components\Select::make('recorrencia')
                    ->label('Repetição')
                    ->options([
                        'nenhuma' => 'Não repete',
                        'diaria' => 'Todos os dias',
                        'semanal' => 'Toda semana',
                        'mensal' => 'Todo mês (mesmo dia)',
                    ])
                    ->default('nenhuma')
                    ->live()
                    ->required(),

                Forms\Components\CheckboxList::make('dias_semana')
                    ->label('Dias da semana')
                    ->options([
                        0 => 'Domingo',
                        1 => 'Segunda',
                        2 => 'Terça',
                        3 => 'Quarta',
                        4 => 'Quinta',
                        5 => 'Sexta',
                        6 => 'Sábado',
                    ])
                    ->columns(4)
                    ->visible(fn (Get $get) => $get('recorrencia') === 'semanal')
                    ->helperText('Se nenhum for marcado, repete no mesmo dia da semana do início.'),

                Forms\Components\DatePicker::make('recorrencia_ate')
                    ->label('Repetir até')
                    ->native(false)
                    ->displayFormat('d/m/Y')
                    ->afterOrEqual('inicio')
                    ->visible(fn (Get $get) => $get('recorrencia') !== 'nenhuma')
                    ->helperText('Deixe em branco para repetir sem data final.'),
                            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('profissional.nome')
                    ->label('Profissional')
                    ->placeholder('Salão inteiro')
                    ->sortable(),

                Tables\Columns\TextColumn::make('inicio')
                    ->label('Início')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('fim')
                    ->label('Fim')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('motivo')
                    ->limit(40),
                Tables\Columns\TextColumn::make('recorrencia')
                ->label('Repetição')
                ->badge()
                ->formatStateUsing(fn (?string $state) => match ($state) {
                    'diaria' => 'Diária',
                    'semanal' => 'Semanal',
                    'mensal' => 'Mensal',
                    default => 'Não repete',
                }),
            ])
            ->defaultSort('inicio', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('profissional_id')
                    ->label('Profissional')
                    ->relationship('profissional', 'nome'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageBloqueioAgendas::route('/'),
        ];
    }
}