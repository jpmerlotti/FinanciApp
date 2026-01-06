<div
    x-data="{
        isEditing: false,
        state: @js($getState()),
        isLoading: false,

        update() {
            this.isEditing = false;
            this.isLoading = true;
            $wire.updateTableColumnState(@js($getName()), @js($recordKey), this.state)
                .then(() => {
                    this.isLoading = false;
                });
        }
    }"
    class="w-full"
>
    <div
        x-show="!isEditing"
        x-on:click="isEditing = true; $nextTick(() => $refs.input.focus())"
        class="cursor-pointer hover:underline decoration-dashed decoration-gray-400 min-h-[20px] flex items-center"
        :class="{'opacity-50': isLoading}"
    >
        {{ $getState() ? \Carbon\Carbon::parse($getState())->format('d/m/Y') : '-' }}

        <x-filament::loading-indicator x-show="isLoading" class="h-4 w-4 ml-2 text-primary-500" />
    </div>

    <input
        x-ref="input"
        x-show="isEditing"
        x-model="state"
        type="date"
        x-on:blur="update()"
        x-on:keydown.enter="update()"
        x-on:keydown.escape="isEditing = false; state = @js($getState())"
        class="
            w-full p-0 text-sm bg-transparent border-none shadow-none focus:ring-0
            dark:text-white
            [color-scheme:light] dark:[color-scheme:dark]
        "
    />
</div>
