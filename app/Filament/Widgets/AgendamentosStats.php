<?php

namespace App\Filament\Widgets;

use App\Models\Agendamento;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AgendamentosStats extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $ativos = fn () => Agendamento::where('status', '!=', 'cancelado');

        return [
            Stat::make('Hoje', $ativos()->whereDate('data_hora', today())->count())
                ->description('agendamentos no dia')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('info'),

            Stat::make('Pendentes', Agendamento::where('status', 'pendente')
                    ->where('data_hora', '>=', now())->count())
                ->description('aguardando confirmação')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('Esta semana', $ativos()->whereBetween('data_hora', [
                    now()->startOfWeek(), now()->endOfWeek(),
                ])->count())
                ->description('de segunda a domingo')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('success'),
        ];
    }
}