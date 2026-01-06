<?php

namespace App\Filament\Widgets;

use App\Enums\TransactionStatuses;
use App\Enums\TransactionTypes;
use App\Models\Transaction;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class PayableCounterpartiesWidget extends Widget
{
    use InteractsWithPageFilters;

    protected string $view = 'filament.widgets.payable-counterparties-widget';
    protected int | string | array $columnSpan = 1;

    public function getViewData(): array
    {
        $startDate = $this->filters['startDate'] ?? now()->startOfMonth();
        $endDate = $this->filters['endDate'] ?? now()->endOfMonth();

        // 1. Get Expenses (Payables) that are Pending or Overdue
        // Grouped by Counterparty (Recipient)
        $transactions = Transaction::query()
            ->with(['counterParty', 'category'])
            ->where('type', TransactionTypes::EXPENSE->value)
            ->whereIn('status', [TransactionStatuses::PENDING, TransactionStatuses::OVERDUE])
            ->whereBetween('due_date', [
                Carbon::parse($startDate),
                Carbon::parse($endDate)->endOfDay()
            ])
            ->when($this->filters['transaction_category_id'] ?? null, fn($q, $val) => $q->where('transaction_category_id', $val))
            ->when($this->filters['recipient'] ?? null, fn($q, $val) => $q->where('recipient', $val))
            ->orderBy('due_date', 'asc')
            ->get();

        // 2. Group by Counterparty ID
        $grouped = $transactions->groupBy('recipient');

        $data = [];

        foreach ($grouped as $recipientId => $items) {
            $name = $items->first()->counterParty?->name ?? 'Sem Contraparte';
            $total = $items->sum('amount_cents');
            $hasOverdue = $items->contains(fn($t) => $t->status === TransactionStatuses::OVERDUE);

            $data[] = [
                'name' => $name,
                'total_cents' => $total,
                'has_overdue' => $hasOverdue,
                'transactions' => $items,
            ];
        }

        // Sort: Overdue first, then by Total Descending
        usort($data, function ($a, $b) {
            if ($a['has_overdue'] !== $b['has_overdue']) {
                return $b['has_overdue'] <=> $a['has_overdue'];
            }
            return $b['total_cents'] <=> $a['total_cents'];
        });

        return [
            'counterparties' => $data,
        ];
    }
}
