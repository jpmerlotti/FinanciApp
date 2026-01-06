<?php

namespace App\Filament\Resources\Transactions\Pages;

use App\Filament\Resources\Transactions\TransactionResource;
use App\Models\Transaction;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

use App\Filament\Resources\Transactions\Concerns\HandlesRecurrence;

class CreateTransaction extends CreateRecord
{
    use HandlesRecurrence;

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
        $this->suffixTitleWithMonth($this->record);

        if (empty($this->repeatData['repeat_transaction']) || empty($this->repeatData['repeat_count'])) {
            return;
        }

        $this->createRecurringTransactions(
            $this->record, 
            (int) $this->repeatData['repeat_count'], 
            $this->repeatData['repeat_interval'] ?? 'monthly'
        );
    }
}
