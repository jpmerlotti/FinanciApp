<?php

namespace App\Filament\Tables\Columns;

use App\Enums\TransactionStatuses;
use Filament\Support\Components\Contracts\HasEmbeddedView;
use Filament\Tables\Columns\SelectColumn;
use Illuminate\View\ComponentAttributeBag;

class StatusSelectColumn extends SelectColumn implements HasEmbeddedView
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->options(TransactionStatuses::class);
    }

    protected function getPreparedAttributes(): ComponentAttributeBag
    {
        return (new ComponentAttributeBag($this->getExtraAttributes()))
            ->except(['x-on:click.prevent', 'x-on:click']);
    }

    public function toEmbeddedHtml(): string
    {
        $name = $this->getName();
        $recordKey = $this->getRecordKey();
        $state = $this->getState();
        
        $enumCase = $state instanceof TransactionStatuses ? $state : TransactionStatuses::tryFrom($state);
        $color = $enumCase?->getColor() ?? 'gray';
        $icon = $enumCase?->getIcon();
        $label = $enumCase?->getLabel() ?? $state;

        // 1. Render main Badge (Trigger)
        $badgeHtml = \Illuminate\Support\Facades\Blade::render(<<<'BLADE'
            <x-filament::badge 
                :color="$color" 
                :icon="$icon"
            >
                {{ $label }}
            </x-filament::badge>
        BLADE, [
            'color' => $color,
            'icon' => $icon,
            'label' => $label,
        ]);

        // 2. Render Options Dropdown List (Styled Badges)
        // We iterate through cases and render a clickable row for each.
        $optionsHtml = \Illuminate\Support\Facades\Blade::render(<<<'BLADE'
            <div class="flex flex-col py-1">
                @foreach($cases as $status)
                    <div 
                        x-on:click="select('{{ $status->value }}')"
                        class="px-2 py-1.5 hover:bg-gray-50 dark:hover:bg-white/5 cursor-pointer flex items-center gap-2 transition"
                    >
                         <x-filament::badge 
                            :color="$status->getColor()" 
                            :icon="$status->getIcon()"
                            class="w-full justify-center"
                        >
                            {{ $status->getLabel() }}
                        </x-filament::badge>
                    </div>
                @endforeach
            </div>
        BLADE, [
            'cases' => TransactionStatuses::cases(),
        ]);

        ob_start(); ?>

        <div 
            wire:ignore.self
            class="relative flex items-center justify-center w-full min-h-[32px] px-2"
            x-data='{
                state: <?php echo json_encode($state instanceof TransactionStatuses ? $state->value : $state); ?>,
                isLoading: false,
                isOpen: false,
                
                select(value) {
                    this.state = value;
                    this.isOpen = false;
                    this.update();
                },

                update() {    
                    console.log("Salvando Status", this.state);
                    this.isLoading = true;
                    $wire.updateTableColumnState(
                        <?php echo json_encode($name); ?>, 
                        <?php echo json_encode($recordKey); ?>, 
                        this.state
                    ).then(() => {
                        this.isLoading = false;
                    }).catch((error) => {
                        this.isLoading = false;
                        console.error("Erro ao salvar:", error);
                    });
                }
            }'
            x-on:click.outside="isOpen = false"
        >
            <!-- Trigger -->
            <div 
                x-on:click="isOpen = !isOpen"
                class="cursor-pointer transition hover:opacity-80"
                x-bind:class="{ 'opacity-50 pointer-events-none': isLoading }"
            >
                <?php echo $badgeHtml; ?>
            </div>

            <!-- Dropdown Menu -->
            <div 
                x-show="isOpen"
                x-transition:enter="transition ease-out duration-100"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-75"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="absolute top-full mt-1 z-50 min-w-[140px] bg-white dark:bg-gray-900 rounded-lg shadow-lg ring-1 ring-gray-900/5 dark:ring-white/10 overflow-hidden text-sm"
                style="display: none;"
            >
                <?php echo $optionsHtml; ?>
            </div>

            <!-- Loading Spinner (Overlay) -->
            <div x-show="isLoading" class="absolute right-0 top-0 bottom-0 flex items-center pr-1 pointer-events-none" style="display: none;">
                <svg class="animate-spin h-3 w-3 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
        </div>

        <?php return ob_get_clean();
    }

    public function updateState(mixed $state): mixed
    {
        $state = parent::updateState($state);

        \Filament\Notifications\Notification::make()
            ->success()
            ->title('Salvo com sucesso!')
            ->body('Status atualizado.')
            ->send();

        return $state;
    }
}
