<?php

namespace App\Filament\Tables\Columns;


use Filament\Tables\Columns\Column;
use Filament\Tables\Columns\Concerns\CanUpdateState;
use Filament\Notifications\Notification;

class TextareaColumn extends Column
{
    use CanUpdateState;

    protected int | Closure | null $rows = null;

    public function rows(int | Closure | null $rows): static
    {
        $this->rows = $rows;

        return $this;
    }

    public function getRows(): ?int
    {
        return $this->evaluate($this->rows);
    }

    protected string $view = 'filament.tables.columns.textarea-column';

    public function updateState(mixed $state): mixed
    {
        // Custom update logic since we are not extending TextInputColumn
        // We use the CanUpdateState trait which provides standard updateTableColumnState
        
        // However, CanUpdateState trait usually expects the column to handle the update call.
        // But Filament's base Column doesn't have the full updateState logic that TextInputColumn has.
        // We need to implement the actual DB update here if we are being called by the Livewire component.
        
        // Actually, Filament handles this if we use $wire.updateTableColumnState.
        // It calls updateTableColumnState on the Table component, which finds the column and calls updateState.
        
        // Let's implement the saving:
        $record = $this->getRecord();
        $name = $this->getName();
        
        $record->update([$name => $state]);

        Notification::make()
            ->success()
            ->title('Salvo com sucesso!')
            ->body('Descrição atualizada.')
            ->send();

        return $state;
    }
}
