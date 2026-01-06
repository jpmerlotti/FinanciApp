<?php

namespace App\Filament\Tables\Columns;

use Filament\Support\Components\Contracts\HasEmbeddedView;
use Filament\Tables\Columns\TextInputColumn;
use Illuminate\View\ComponentAttributeBag;

class MoneyInputColumn extends TextInputColumn implements HasEmbeddedView
{
    protected function getFormattedState(): ?string
    {
        $state = $this->getState();

        if (is_numeric($state)) {
            return number_format($state / 100, 2, ',', '.');
        }

        return $state;
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
        $state = $this->getFormattedState();
        $attributes = $this->getPreparedAttributes();

        ob_start(); ?>

        <div class="fi-ta-col">
            <div 
                wire:ignore.self
                class="fi-ta-text-input"
                x-data='{
                    state: <?php echo json_encode($state); ?>,
                    isLoading: false,
                    
                    format(value) {
                         if (!value) return "";
                         value = value.replace(/\D/g, "");
                         return (Number(value) / 100).toLocaleString("pt-BR", {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        });
                    },

                    unformat(value) {
                        return parseInt(value.replace(/\D/g, ""));
                    },

                    update() {    
                        console.log("Salvando", this.state);
                        let cleanValue = this.unformat(this.state);

                        this.isLoading = true;
                        $wire.updateTableColumnState(
                            <?php echo json_encode($name); ?>, 
                            <?php echo json_encode($recordKey); ?>, 
                            cleanValue
                        ).then(() => {
                            this.isLoading = false;
                        }).catch((error) => {
                            this.isLoading = false;
                            console.error("Erro ao salvar:", error);
                        });
                    }
                }'
            >
                <div <?php echo $attributes->class(['fi-input-wrp flex !shadow-none !ring-0 !bg-transparent']); ?>>
                    <span class="pl-2 flex items-center text-gray-500 sm:text-sm">R$</span>
                    <input
                        x-model="state"
                        x-on:input="state = format($event.target.value)"
                        x-on:change="update()"
                        
                        wire:loading.attr="disabled"
                        type="text"
                        x-on:click.stop
                        class="fi-input block w-full !border-none !shadow-none !bg-transparent !ring-0 !py-1.5 text-sm text-gray-950 outline-none transition duration-75 placeholder:text-gray-400 disabled:text-gray-500 disabled:[-webkit-text-fill-color:theme(colors.gray.500)] disabled:placeholder:[-webkit-text-fill-color:theme(colors.gray.400)] dark:text-white dark:placeholder:text-gray-500 dark:disabled:text-gray-400 dark:disabled:[-webkit-text-fill-color:theme(colors.gray.500)] dark:disabled:placeholder:[-webkit-text-fill-color:theme(colors.gray.500)] !sm:text-sm !sm:leading-6"
                        x-bind:class="{ '"!'opacity-50 '!'cursor-wait'"': isLoading }"
                        x-bind:disabled="isLoading"
                    />
                    
                     <div x-show="isLoading" class="absolute right-2 top-2" style="display: none;">
                        <svg class="animate-spin h-4 w-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>
                </div>
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
            ->body('Valor atualizado.')
            ->send();

        return $state;
    }
}
