<div
    x-data="{
        isEditing: false,
        state: @js($getState()),

        update() {
            this.isEditing = false;
            $wire.updateTableColumnState(@js($getName()), @js($recordKey), this.state);
        }
    }"
    class="w-full"
>
    <div
        x-show="!isEditing"
        x-on:click="isEditing = true"
        class="cursor-pointer"
    >
        @php
            $color = match($getState()) {
                'paid' => 'success',
                'pending' => 'warning',
                'overdue' => 'danger',
                default => 'gray'
            };

            // Tradução manual ou via Enum
            $label = App\Enums\TransactionStatuses::tryFrom($getState()?->getLabel()) ?? $getState();
        @endphp

        <x-filament::badge :color="$color">
            {{ $label }}
        </x-filament::badge>
    </div>

    <select
        x-show="isEditing"
        x-model="state"
        x-on:change="update()"
        x-on:blur="isEditing = false"
        class="
            w-full p-0 text-xs font-medium bg-transparent border-none focus:ring-0 cursor-pointer
            dark:bg-gray-900 dark:text-white
        "
    >
        @foreach(App\Enums\TransactionStatuses::cases() as $status)
            <option value="{{ $status->value }}">{{ $status->getLabel() }}</option>
        @endforeach
    </select>
</div>
