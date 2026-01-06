<div
    wire:ignore.self
    class="fi-ta-text-input"
    x-data='{
        state: @json($getState()),
        isLoading: false,
        
        update() {    
            this.isLoading = true;
            $wire.updateTableColumnState(
                @json($getName()), 
                @json($recordKey), 
                this.state
            ).then(() => {
                this.isLoading = false;
            }).catch((error) => {
                this.isLoading = false;
                console.error("Erro ao salvar:", error);
            });
        }
    }'
>
    <div class="fi-input-wrp flex !shadow-none !ring-0 !bg-transparent">
        <textarea
            x-model="state"
            x-on:change="update()"
            wire:loading.attr="disabled"
            rows="{{ $getRows() ?? 2 }}"
            x-on:click.stop
            class="fi-input block w-full !border-none !shadow-none !bg-transparent !ring-0 !py-1.5 text-base text-sm text-gray-950 outline-none transition duration-75 placeholder:text-gray-400 disabled:text-gray-500 disabled:[-webkit-text-fill-color:theme(colors.gray.500)] disabled:placeholder:[-webkit-text-fill-color:theme(colors.gray.400)] dark:text-white dark:placeholder:text-gray-500 dark:disabled:text-gray-400 dark:disabled:[-webkit-text-fill-color:theme(colors.gray.400)] dark:disabled:placeholder:[-webkit-text-fill-color:theme(colors.gray.500)] !sm:text-sm !sm:leading-6 resize-none"
            x-bind:class="{ '!\'opacity-50 \'!\'cursor-wait\'': isLoading }"
            x-bind:disabled="isLoading"
        ></textarea>
        
            <div x-show="isLoading" class="absolute right-2 top-2" style="display: none;">
            <svg class="animate-spin h-4 w-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </div>
    </div>
</div>
