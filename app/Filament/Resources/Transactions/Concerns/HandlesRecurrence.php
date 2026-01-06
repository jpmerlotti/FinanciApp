<?php

namespace App\Filament\Resources\Transactions\Concerns;

use App\Models\Transaction;
use Carbon\Carbon;
use Filament\Facades\Filament;

trait HandlesRecurrence
{
    protected function createRecurringTransactions(Transaction $parent, int $count, string $interval): void
    {
        $tenantId = Filament::getTenant()->id;
        $groupId = $parent->recurring_group_id ?? str()->uuid();
        $totalInstallments = $parent->total_installments ?? ($count + 1);

        // Ensure parent has the correct group and installments info if not set
        if ($parent->recurring_group_id === null || $parent->total_installments === null) {
            $parent->update([
                'recurring_group_id' => $groupId,
                'installment_number' => 1,
                'total_installments' => $totalInstallments,
            ]);
        }

        // Use Due Date as the anchor
        $baseDate = $parent->due_date ? Carbon::parse($parent->due_date) : now();
        $originalTitle = $parent->title;

        // Strip existing month suffix if present to avoid "Salary - JAN - FEB"
        $originalTitle = preg_replace('/ - [a-zA-ZçÇ]{3}\.?$/i', '', $originalTitle);

        $newTransactions = [];

        for ($i = 1; $i <= $count; $i++) {

            $nextDate = $baseDate->copy();

            match ($interval) {
                'weekly'       => $nextDate->addWeeks($i),
                'monthly'      => $nextDate->addMonthsNoOverflow($i),
                'semiannually' => $nextDate->addMonthsNoOverflow($i * 6),
                'annually'     => $nextDate->addYears($i),
            };

            // Calculate Tag based on Due Date
            $monthTag = str($nextDate->locale('pt_BR')->translatedFormat('M'))->upper();
            $monthTag = trim($monthTag, '.');

            $newTransactions[] = [
                'organization_id'    => $tenantId,
                'recurring_group_id' => $groupId,
                'installment_number' => $i + 1,
                'total_installments' => $totalInstallments,

                'title'            => "{$originalTitle} - {$monthTag}",
                'type'             => $parent->type,
                'transaction_category_id' => $parent->transaction_category_id,
                'amount_cents'     => $parent->amount_cents,
                'status'           => 'pending',
                'due_date'         => $nextDate,
                'paid_at'          => null,
                'cancelled_at'     => null,
                'recipient'        => $parent->recipient,
                'description'      => $parent->description,
                'payment_proof'    => null, 
                'created_at'       => now(),
                'updated_at'       => now(),
            ];
        }

        if (! empty($newTransactions)) {
            Transaction::insert($newTransactions);
        }
    }
    
    /**
     * Updates future pending transactions in the same group.
     */
    protected function updateFutureRecurringTransactions(Transaction $parent): void
    {
        if (! $parent->recurring_group_id) {
            return;
        }

        // Clean title for propagation
        $baseTitle = preg_replace('/ - [a-zA-ZçÇ]{3}\.?$/i', '', $parent->title);

        $futureTransactions = Transaction::where('recurring_group_id', $parent->recurring_group_id)
            ->where('id', '!=', $parent->id)
            ->where('status', 'pending')
            ->where('due_date', '>', $parent->due_date) // Safety check: only future
            ->get();

        foreach ($futureTransactions as $transaction) {
            // Re-calculate title with its specific month
            $tagDate = $transaction->due_date ? Carbon::parse($transaction->due_date) : now();
            $monthTag = str($tagDate->locale('pt_BR')->translatedFormat('M'))->upper();
            $monthTag = trim($monthTag, '.');
            $newTitle = "{$baseTitle} - {$monthTag}";

            $transaction->update([
                'title'            => $newTitle,
                'type'             => $parent->type,
                'transaction_category_id' => $parent->transaction_category_id,
                'amount_cents'     => $parent->amount_cents,
                'recipient'        => $parent->recipient,
                'description'      => $parent->description,
                // We do NOT update dates or status status implicitly to avoid overriding manual moves
            ]);
            
           // Tags sync removed used categories now (One-to-Many automatically updated via column update)
        }
    }
    /**
     * Appends the month (e.g. " - JAN") to the transaction title if not already present.
     */
    protected function suffixTitleWithMonth(Transaction $record): void
    {
         $baseDueDate = $record->due_date ? Carbon::parse($record->due_date) : now();
         
         $initialMonth = str($baseDueDate->locale('pt_BR')->translatedFormat('M'))->upper();
         $initialMonth = trim($initialMonth, '.');

         // Prevent double suffix
         if (! str_ends_with(strtoupper($record->title), " - {$initialMonth}")) {
              $record->update([
                 'title' => "{$record->title} - {$initialMonth}",
             ]);
         }
    }
}
