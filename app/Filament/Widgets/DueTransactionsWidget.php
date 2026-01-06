<?php

namespace App\Filament\Widgets;

use App\Enums\TransactionStatuses;
use App\Enums\TransactionTypes;
use App\Models\Transaction;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Builder;

class DueTransactionsWidget extends Widget
{
    use \Filament\Widgets\Concerns\InteractsWithPageFilters;

    protected int | string | array $columnSpan = 1;

    protected string $view = 'filament.widgets.due-transactions-widget';

    protected function getViewData(): array
    {
        $transactions = Transaction::query()
            ->where(function (Builder $query) {
                // 1. Expense due TODAY
                $query->where(function ($q) {
                    $q->where('type', TransactionTypes::EXPENSE)
                        ->where('status', TransactionStatuses::PENDING)
                        ->whereDate('due_date', now()->today());
                })
                // 2. Income due YESTERDAY
                ->orWhere(function ($q) {
                        $q->where('type', TransactionTypes::INCOME)
                        ->where('status', TransactionStatuses::PENDING)
                        ->whereDate('due_date', now()->subDay());
                });
            })
            ->orderBy('due_date', 'asc')
            ->when($this->filters['transaction_category_id'] ?? null, fn($q, $val) => $q->where('transaction_category_id', $val))
            ->when($this->filters['recipient'] ?? null, fn($q, $val) => $q->where('recipient', $val))
            ->get();

        return [
            'transactions' => $transactions,
        ];
    }
    public function markAsPaid(int $id): void
    {
        $transaction = Transaction::find($id);

        if (! $transaction) {
            return;
        }

        $transaction->update([
            'status' => TransactionStatuses::COMPLETED,
            'paid_at' => now(),
        ]);

        \Filament\Notifications\Notification::make()
            ->title('Transação concluída')
            ->success()
            ->send();
    }
}
