<?php

namespace App\Listeners;

use App\Enums\TransactionStatuses;
use App\Enums\TransactionTypes;
use App\Models\Transaction;
use Filament\Notifications\Notification;
use Filament\Notifications\Actions\Action;
use Illuminate\Auth\Events\Login;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CheckDueTransactions
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(Login $event): void
    {
        // 1. Expenses due TODAY or OVERDUE (Pending)
        $expensesCount = Transaction::query()
            ->where('type', TransactionTypes::EXPENSE)
            ->where('status', TransactionStatuses::PENDING)
            ->whereDate('due_date', '<=', now()->today())
            ->count();

        // 2. Income due TODAY or OVERDUE (Pending)
        $incomesCount = Transaction::query()
            ->where('type', TransactionTypes::INCOME)
            ->where('status', TransactionStatuses::PENDING)
            ->whereDate('due_date', '<=', now()->today())
            ->count();

        if ($expensesCount === 0 && $incomesCount === 0) {
            return;
        }

        // Build Title and Body
        $title = 'Resumo Financeiro';
        $body = [];

        if ($expensesCount > 0) {
            $body[] = "🔴 <b>{$expensesCount}</b> conta(s) a pagar vencidas/vencendo hoje.";
        }
        if ($incomesCount > 0) {
            $body[] = "🟢 <b>{$incomesCount}</b> cobrança(s) a receber vencidas/vencendo hoje.";
        }

        Notification::make()
            ->title($title)
            ->body(implode('<br>', $body))
            ->warning()
            ->persistent()
            ->actions([
                Action::make('view')
                    ->label('Ir para o Dashboard')
                    ->url(route('filament.admin.pages.dashboard'))
                    ->button(),
            ])
            ->send();
    }
}
