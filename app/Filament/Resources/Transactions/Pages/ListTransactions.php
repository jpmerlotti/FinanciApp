<?php

namespace App\Filament\Resources\Transactions\Pages;

use App\Filament\Resources\Transactions\TransactionResource;
use App\Filament\Resources\Transactions\Widgets\FinancialHealthOverview;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListTransactions extends ListRecords
{
    use ExposesTableToWidgets;
    protected static string $resource = TransactionResource::class;

    protected function getHeaderActions(): array
    {
        // $isStatsVisible = session('show_stats', true);

        return [
            // Action::make('toggleStats')
            //     ->label($isStatsVisible ? 'Ocultar Resumo' : 'Mostrar Resumo')
            //     ->icon($isStatsVisible ? 'heroicon-m-eye-slash' : 'heroicon-m-chart-bar')
            //     ->color('gray')
            //     ->size('sm')
            //     ->action(function () use ($isStatsVisible) {
            //         session()->put('show_stats', !$isStatsVisible);

            //         return redirect(request()->header('Referer'));
            //     }),

            CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        $currentYear = now()->year;
        $nextYear = $currentYear + 1;
        $pastYear = $currentYear - 1;

        $tabs = [];
            
        $tabs[] = Tab::make((string) $currentYear)
            ->icon('heroicon-m-calendar')
            ->query(fn (Builder $query) => $query
                ->whereYear('due_date', $currentYear)
            );
        

        for ($month = 1; $month <= 12; $month++) {

            $date = Carbon::createFromDate($currentYear, $month, 1);
            $monthName = ucfirst($date->locale('pt_BR')->monthName);

            $key = strtolower($date->format('M'));

            $tabs[$key] = Tab::make($monthName)
                ->query(fn (Builder $query) => $query
                    ->whereYear('due_date', $currentYear)
                    ->whereMonth('due_date', $month)
                );
        }

        $tabs['next_year'] = Tab::make((string) $nextYear)
            ->icon('heroicon-m-calendar')
            ->query(fn (Builder $query) => $query
                ->whereYear('due_date', $nextYear)
            );

        $tabs['all'] = Tab::make('Geral')
            ->icon('heroicon-m-bars-3');

        return $tabs;
    }

    public function getDefaultActiveTab(): string | int | null
    {
        return strtolower(now()->format('M'));
    }

    protected function getHeaderWidgets(): array
    {
        if (! session('show_stats', true)) {
            return [];
        }

        return [
            FinancialHealthOverview::class
        ];
    }
}
