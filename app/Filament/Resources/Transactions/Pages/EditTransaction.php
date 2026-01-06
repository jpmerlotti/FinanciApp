<?php

namespace App\Filament\Resources\Transactions\Pages;

use App\Filament\Resources\Transactions\TransactionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\Transactions\Concerns\HandlesRecurrence;

class EditTransaction extends EditRecord
{
    protected static string $resource = TransactionResource::class;

    use HandlesRecurrence;

    protected array $repeatData = [];

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->repeatData['repeat_transaction'] = $data['repeat_transaction'] ?? false;
        $this->repeatData['repeat_count']       = $data['repeat_count'] ?? null;
        $this->repeatData['repeat_interval']    = $data['repeat_interval'] ?? 'monthly';

        // We clean up non-column data, though usually Filament ignores extra if strict is off,
        // but explicit unset is safer if they are not in $fillable
        unset($data['repeat_transaction'], $data['repeat_count'], $data['repeat_interval']);

        return $data;
    }

    protected function afterSave(): void
    {
        // Scenario 1: New Recurrence (was not recurring, now enabled)
        // Check if user enabled repeat AND record doesn't have a group yet
        if (
            ! empty($this->repeatData['repeat_transaction']) && 
            ! empty($this->repeatData['repeat_count']) && 
            ! $this->record->recurring_group_id
        ) {
            $this->suffixTitleWithMonth($this->record);
            
            $this->createRecurringTransactions(
                $this->record, 
                (int) $this->repeatData['repeat_count'], 
                $this->repeatData['repeat_interval'] ?? 'monthly'
            );
            return;
        }

        // Scenario 2: Batch Update (is part of a group)
        if ($this->record->recurring_group_id) {
            $this->updateFutureRecurringTransactions($this->record);
        }
    }
}
