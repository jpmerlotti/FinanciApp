<?php

namespace App\Filament\Support;

class NotionStyle
{
    public static function input(string $name): array
    {
        return [
            'class' => '
                !border-0 !shadow-none !bg-transparent !px-0 !py-0
                !text-sm hover:!bg-gray-50 dark:hover:!bg-white/5
                focus:!ring-0 cursor-text transition-colors rounded-sm
            '
        ];
    }

    public static function select(): array
    {
        return [
            'class' => '
                !border-0 !shadow-none !bg-transparent !px-0 !py-0
                !text-sm !font-medium cursor-pointer
                focus:!ring-0
            ',
        ];
    }
}
