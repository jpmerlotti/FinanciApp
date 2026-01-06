<?php

namespace App\Filament\Resources\Transactions\Widgets;

use App\Enums\TransactionTypes;
use App\Filament\Resources\Transactions\Pages\ListTransactions;
use App\Models\Transaction;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Number;

class Balance extends StatsOverviewWidget
{
    use InteractsWithPageTable;

    protected int | string | array $columnSpan = 'full';
    protected static ?int $sort = 1;

    protected function getColumns(): int
    {
        return 5;
    }

    protected function getStats(): array
    {
        $yearQuery = Transaction::query()->whereYear('due_date', now()->year);

        $yearIncome  = (clone $yearQuery)->where('type', TransactionTypes::INCOME->value)->sum('amount_cents');
        $yearExpense = -1 * (clone $yearQuery)->where('type', TransactionTypes::EXPENSE->value)->sum('amount_cents');
        $yearBalance = $yearIncome + $yearExpense;

        $currentYearIncomes = (clone $yearQuery)
            ->where('type', TransactionTypes::INCOME)
            ->count();

        $currentYearExpenses = (clone $yearQuery)
            ->where('type', TransactionTypes::EXPENSE)
            ->count();

        $tabQuery = $this->getPageTableQuery();

        $tabIncome = 0; $tabExpense = 0; $tabBalance = 0;

        if ($tabQuery) {
            $tabIncome  = (clone $tabQuery)->where('type', TransactionTypes::INCOME->value)->sum('amount_cents');
            $tabExpense = -1 * (clone $tabQuery)->where('type', TransactionTypes::EXPENSE->value)->sum('amount_cents');
            $tabBalance = $tabIncome + $tabExpense;

            $tabIncomes = (clone $tabQuery)->where('type', TransactionTypes::INCOME)->count();
            $tabExpenses = (clone $tabQuery)->where('type', TransactionTypes::EXPENSE)->count();
        }

        return [
            Stat::make('Balanço (' . now()->format('Y') . ')', Number::currency($yearBalance / 100, 'BRL'))
                ->description('Acumulado do Ano')
                ->descriptionIcon('heroicon-m-calendar')
                ->color($yearBalance >= 0 ? 'success' : 'danger'),

            Stat::make('Entradas Totais (' . now()->format('Y') . ')', $currentYearIncomes)
                ->color('success')
                ->icon('heroicon-m-arrow-down-circle'),

            Stat::make('Entradas R$ (' . now()->format('Y') . ')', Number::currency($yearIncome / 100, 'BRL'))
                ->color('success')
                ->icon('heroicon-m-arrow-down-circle'),

            Stat::make('Saídas Totais (' . now()->format('Y') . ')', $currentYearExpenses)
                ->color('success')
                ->icon('heroicon-m-arrow-up-circle'),

            Stat::make('Saídas R$ (' . now()->format('Y') . ')', Number::currency(abs($yearExpense) / 100, 'BRL'))
                ->color('danger')
                ->icon('heroicon-m-arrow-up-circle'),

            Stat::make("Balanço (Período)", Number::currency($tabBalance / 100, 'BRL'))
                ->description('Filtro da Tabela')
                ->descriptionIcon('heroicon-m-funnel')
                ->chart($this->getChartData($tabQuery))
                ->color($tabBalance >= 0 ? 'info' : 'warning'),

            Stat::make('Entradas Totais (Período)', $tabIncomes ?? 0)
                ->color('success')
                ->extraAttributes(['class' => 'opacity-80']),

            Stat::make('Entradas R$ (Período)', Number::currency($tabIncome / 100, 'BRL'))
                ->color('success')
                ->extraAttributes(['class' => 'opacity-80']),

            Stat::make('Saídas Totais (Período)', $tabExpenses ?? 0)
                ->color('danger')
                ->extraAttributes(['class' => 'opacity-80']),

            Stat::make('Saídas R$ (Período)', Number::currency(abs($tabExpense) / 100, 'BRL'))
                ->color('danger')
                ->extraAttributes(['class' => 'opacity-80']),
        ];
    }

    private function getChartData(?Builder $query): array
    {
        if (!$query) return [];

        return (clone $query)
            ->latest('due_date')
            ->take(7)
            ->pluck('amount_cents')
            ->map(fn ($val) => $val / 100)
            ->toArray();
    }

    protected function getTablePage(): string
    {
        return ListTransactions::class;
    }
}
