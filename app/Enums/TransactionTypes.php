<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum TransactionTypes: string implements HasLabel, HasColor, HasIcon
{
    case INCOME = 'income';
    case EXPENSE = 'expense';

    public function getLabel(): string
    {
        return __('transaction-types.' . str($this->value)->title());
    }

    public function getColor(): ?string
    {
        return match ($this) {
            self::INCOME => 'success',
            self::EXPENSE => 'danger',
            default => null
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::EXPENSE => 'heroicon-o-arrow-up-circle',
            self::INCOME => 'heroicon-o-arrow-down-circle',
            default => null,
        };
    }
}
