<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

enum TransactionStatuses: string implements HasLabel, HasColor, HasIcon
{

    case PENDING = 'pending';
    case COMPLETED = 'completed';
    case CANCELED = 'canceled';
    case OVERDUE = 'overdue';

    public function getLabel(): string | Htmlable | null
    {
        return __('transaction-statuses.' . str($this->value)->title());
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::PENDING => 'gray',
            self::COMPLETED => 'success',
            self::CANCELED => 'warning',
            self::OVERDUE => 'danger'
        };
    }

    public function getIcon(): string | Heroicon | Htmlable | null
    {
        return match ($this) {
            self::PENDING => Heroicon::OutlinedClock,
            self::COMPLETED => Heroicon::OutlinedCheckBadge,
            self::CANCELED => Heroicon::OutlinedXCircle,
            self::OVERDUE => Heroicon::OutlinedExclamationTriangle
        };
    }

    public static function default(): string
    {
        return self::PENDING->value;
    }
}
