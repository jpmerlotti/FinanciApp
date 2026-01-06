<?php

namespace App\Filament\Widgets;

use App\Enums\TransactionStatuses;
use App\Enums\TransactionTypes;
use App\Models\Transaction;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Number;

class FinancialStatsOverview extends Widget
{
    use InteractsWithPageFilters;
    
    protected int | string | array $columnSpan = 1;

    protected string $view = 'filament.widgets.financial-stats-overview';

    public function getViewData(): array
    {
        // 1. Get Filters
        $startDate = $this->filters['startDate'] ?? now()->startOfMonth();
        $endDate = $this->filters['endDate'] ?? now()->endOfMonth();

        // 2. Base Query (Scoped to Period and Income)
        $query = Transaction::query()
            ->where('type', TransactionTypes::INCOME)
            ->whereBetween('due_date', [
                Carbon::parse($startDate),
                Carbon::parse($endDate)->endOfDay()
            ])
            ->when($this->filters['transaction_category_id'] ?? null, fn($q, $val) => $q->where('transaction_category_id', $val))
            ->when($this->filters['recipient'] ?? null, fn($q, $val) => $q->where('recipient', $val));

        // 3. Compute Metrics
        
        // "Recebidas" (Paid)
        $receivedQuery = (clone $query)->where('status', TransactionStatuses::COMPLETED);
        $receivedAmount = $receivedQuery->sum('amount_cents');
        $receivedCount = $receivedQuery->count();

        // "Vencidas" (Overdue)
        $overdueQuery = (clone $query)->where('status', TransactionStatuses::OVERDUE);
        $overdueAmount = $overdueQuery->sum('amount_cents');
        $overdueCount = $overdueQuery->count();

        // "Aguardando pagamento" (Pending)
        $pendingQuery = (clone $query)->where('status', TransactionStatuses::PENDING);
        $pendingAmount = $pendingQuery->sum('amount_cents');
        $pendingCount = $pendingQuery->count();

        // "Canceladas" (Canceled)
        $canceledQuery = (clone $query)->where('status', TransactionStatuses::CANCELED);
        $canceledAmount = $canceledQuery->sum('amount_cents');
        $canceledCount = $canceledQuery->count();

        // 4. Return Stats as Array data for custom view
        return [
            'stats' => [
                [
                    'label' => 'Recebidas',
                    'value' => Number::currency($receivedAmount / 100, 'BRL'),
                    'description' => "{$receivedCount} Cobrança(s)",
                    'icon' => 'heroicon-m-check-circle',
                    'color' => TransactionStatuses::COMPLETED->getColor(),
                ],
                [
                    'label' => 'Canceladas',
                    'value' => Number::currency($canceledAmount / 100, 'BRL'),
                    'description' => "{$canceledCount} Cobrança(s)",
                    'icon' => 'heroicon-m-x-circle',
                    'color' => TransactionStatuses::CANCELED->getColor(),
                ],
                [
                    'label' => 'Aguardando pagamento',
                    'value' => Number::currency($pendingAmount / 100, 'BRL'),
                    'description' => "{$pendingCount} Cobrança(s)",
                    'icon' => 'heroicon-m-clock',
                    'color' => TransactionStatuses::PENDING->getColor(),
                ],
                [
                    'label' => 'Vencidas',
                    'value' => Number::currency($overdueAmount / 100, 'BRL'),
                    'description' => "{$overdueCount} Cobrança(s)",
                    'icon' => 'heroicon-m-exclamation-triangle',
                    'color' => TransactionStatuses::OVERDUE->getColor(),
                ],
            ]
        ];
    }
}
