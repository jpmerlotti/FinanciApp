<?php

namespace App\Filament\Resources\Transactions\Pages;

use App\Filament\Resources\Transactions\TransactionResource;
use App\Models\Transaction;
use Carbon\Carbon;
use Filament\Resources\Pages\CreateRecord;

class CreateTransaction extends CreateRecord
{
    protected static string $resource = TransactionResource::class;
    protected array $repeatData = [];

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->repeatData['repeat_transaction'] = $data['repeat_transaction'] ?? false;
        $this->repeatData['repeat_count']       = $data['repeat_count'] ?? null;
        $this->repeatData['repeat_interval']    = $data['repeat_interval'] ?? 'monthly';

        unset($data['repeat_transaction'], $data['repeat_count'], $data['repeat_interval']);

        return $data;
    }

    protected function afterCreate(): void
    {
        if (empty($this->repeatData['repeat_transaction']) || empty($this->repeatData['repeat_count'])) {
            return;
        }

        $groupId = str()->uuid();
        $count = (int) $this->repeatData['repeat_count'];
        $interval = $this->repeatData['repeat_interval'] ?? 'monthly';
        $totalInstallments = $count + 1;

        $this->record->update([
            'recurring_group_id' => $groupId,
            'installment_number' => 1,
            'total_installments' => $totalInstallments,
        ]);

        $baseDate = Carbon::parse($this->record->transaction_date);
        $newTransactions = [];

        for ($i = 1; $i <= $count; $i++) {

            $nextDate = $baseDate->copy();
            match ($interval) {
                'weekly'       => $nextDate->addWeeks($i),
                'monthly'      => $nextDate->addMonthsNoOverflow($i),
                'semiannually' => $nextDate->addMonthsNoOverflow($i * 6),
                'annually'     => $nextDate->addYears($i),
            };

            $newTransactions[] = [
                'recurring_group_id' => $groupId,
                'installment_number' => $i + 1,
                'total_installments' => $totalInstallments,

                'title'            => $this->record->title,
                'type'             => $this->record->type,
                'amount_cents'     => $this->record->amount_cents,
                'status'           => 'pending',
                'transaction_date' => $nextDate,
                'recipient'        => $this->record->recipient,
                'description'      => $this->record->description,
                'payment_proof'    => null,
                'created_at'       => now(),
                'updated_at'       => now(),
            ];
        }

        if (! empty($newTransactions)) {
            Transaction::insert($newTransactions);
        }
    }
}
