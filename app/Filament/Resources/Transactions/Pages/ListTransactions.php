<?php

namespace App\Filament\Resources\Transactions\Pages;

use App\Filament\Resources\Transactions\TransactionResource;
use App\Models\Transaction;
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
        $isStatsVisible = session('show_stats', true);

        return [
            Action::make('toggleStats')
                ->label($isStatsVisible ? 'Ocultar Resumo' : 'Mostrar Resumo')
                ->icon($isStatsVisible ? 'heroicon-m-eye-slash' : 'heroicon-m-chart-bar')
                ->color('gray')
                ->size('sm') // Botão discreto
                ->action(function () use ($isStatsVisible) {
                    // Inverte o valor na sessão
                    session()->put('show_stats', !$isStatsVisible);

                    // Recarrega a página para aplicar a mudança (evita complexidade de re-render do Livewire)
                    return redirect(request()->header('Referer'));
                }),

            CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        $currentYear = now()->year;
        $nextYear = $currentYear + 1;

        $tabs = [];

        $tabs[] = Tab::make((string) $currentYear)
            ->icon('heroicon-m-calendar')
            ->query(fn (Builder $query) => $query
                ->whereYear('transaction_date', $currentYear)
            )
            ->badge(
                Transaction::whereYear('transaction_date', $currentYear)
                    ->count()
            );

        for ($month = 1; $month <= 12; $month++) {

            $date = Carbon::createFromDate($currentYear, $month, 1);
            $monthName = ucfirst($date->locale('pt_BR')->monthName);

            $key = strtolower($date->format('M'));

            $tabs[$key] = Tab::make($monthName)
                ->query(fn (Builder $query) => $query
                    ->whereYear('transaction_date', $currentYear)
                    ->whereMonth('transaction_date', $month)
                )
                ->badge(
                    Transaction::whereYear('transaction_date', $currentYear)
                        ->whereMonth('transaction_date', $month)
                        ->count()
                );
        }

        $tabs['next_year'] = Tab::make((string) $nextYear)
            ->icon('heroicon-m-calendar')
            ->query(fn (Builder $query) => $query
                ->whereYear('transaction_date', $nextYear)
            )
            ->badge(
                Transaction::whereYear('transaction_date', $nextYear)
                    ->count()
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
            TransactionResource\Widgets\Balance::class,
        ];
    }
}
