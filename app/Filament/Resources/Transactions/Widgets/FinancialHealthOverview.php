<?php

namespace App\Filament\Resources\Transactions\Widgets;

use App\Enums\TransactionStatuses;
use App\Enums\TransactionTypes;
use App\Filament\Resources\Transactions\Pages\ListTransactions;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\Widget;
use Illuminate\Support\Number;

class FinancialHealthOverview extends Widget
{
    protected string $view = 'filament.resources.transactions.widgets.financial-health-overview';

    use \Filament\Widgets\Concerns\InteractsWithPageFilters;

    protected int | string | array $columnSpan = 'full';

    // public ?array $tableColumnSearches = [];



    protected function getViewData(): array
    {
        // Always build a fresh query for the dashboard/widget context
        $query = \App\Models\Transaction::query();

        // Apply Dashboard Filters (if present)
        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? null;
        $category = $this->filters['transaction_category_id'] ?? null;
        $recipient = $this->filters['recipient'] ?? null;

        if ($startDate && $endDate) {
            $query->whereBetween('due_date', [
                \Carbon\Carbon::parse($startDate),
                \Carbon\Carbon::parse($endDate)->endOfDay()
            ]);
        }
        
        $query->when($category, fn($q, $val) => $q->where('transaction_category_id', $val))
              ->when($recipient, fn($q, $val) => $q->where('recipient', $val));

        $incomeQuery = (clone $query)->where('type', TransactionTypes::INCOME);
        $incomeTotal = $incomeQuery->sum('amount_cents');
        $incomePaid = (clone $incomeQuery)->where('status', TransactionStatuses::COMPLETED)->sum('amount_cents');
        $incomePending = $incomeTotal - $incomePaid;
        $incomePct = $incomeTotal > 0 ? ($incomePaid / $incomeTotal) * 100 : 0;

        $expenseQuery = (clone $query)->where('type', TransactionTypes::EXPENSE);
        $expenseTotal = abs($expenseQuery->sum('amount_cents'));
        $expensePaid = abs((clone $expenseQuery)->where('status', TransactionStatuses::COMPLETED)->sum('amount_cents'));
        $expensePending = $expenseTotal - $expensePaid;
        $expensePct = $expenseTotal > 0 ? ($expensePaid / $expenseTotal) * 100 : 0;

        $realBalance = $incomePaid - $expensePaid;
        $balanceEstimated = $incomeTotal - $expenseTotal;

        return [
            'income' => [
                'total' => number_format($incomeTotal / 100, 2, ',', '.'),
                'paid' => number_format($incomePaid / 100, 2, ',', '.'),
                'pending' => number_format($incomePending / 100, 2, ',', '.'),
                'paid_pct' => round($incomePct),
            ],
            'expense' => [
                'total' => number_format($expenseTotal / 100, 2, ',', '.'),
                'paid' => number_format($expensePaid / 100, 2, ',', '.'),
                'pending' => number_format($expensePending / 100, 2, ',', '.'),
                'paid_pct' => round($expensePct),
            ],
            'balance' => [
                'real' => number_format($realBalance / 100, 2, ',', '.'),
                'is_positive' => $realBalance >= 0,
                'estimated' => number_format($balanceEstimated / 100, '2', ',', '.'),
            ],
        ];
    }
}
