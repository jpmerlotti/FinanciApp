<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            Contas do Dia
        </x-slot>

        @if($transactions->isEmpty())
            <div class="fi-ta-empty-state px-6 py-12">
                <div class="fi-ta-empty-state-content mx-auto grid max-w-lg justify-items-center text-center">
                    <p class="fi-ta-empty-state-description text-sm text-gray-500 dark:text-gray-400">
                        Nenhuma conta vencendo hoje ou recebimento recente pendente.
                    </p>
                </div>
            </div>
        @else
            <div class="flex flex-col gap-y-4">
                @foreach($transactions as $transaction)
                    <div
                        class="flex items-center justify-between p-4 rounded-lg bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                        <div class="flex flex-col gap-1">
                            <span class="text-sm font-medium text-gray-950 dark:text-white">
                                {{ $transaction->title }}
                            </span>

                            <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                                <span>{{ $transaction->due_date?->format('d/m/Y') }}</span>

                                @if($transaction->recipient)
                                    <span>&bull;</span>
                                    <span>{{ $transaction->recipient }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="flex flex-col items-end gap-1">
                            @php
                                $color = $transaction->type->getColor();
                                $icon = $transaction->type->getIcon();
                            @endphp

                            <span class="text-sm font-bold" style="color: rgb(var(--{{ $color }}-600))">
                                {{ $transaction->type === \App\Enums\TransactionTypes::EXPENSE ? '-' : '+' }}
                                {{ number_format($transaction->amount_cents / 100, 2, ',', '.') }}
                            </span>
                        </div>

                        <div class="ml-4 border-l pl-4 border-gray-200 dark:border-gray-700">
                             <x-filament::icon-button
                                icon="heroicon-m-check"
                                color="success"
                                tooltip="Marcar como Pago/Recebido"
                                wire:click="markAsPaid({{ $transaction->id }})"
                                wire:confirm="Confirmar o pagamento/recebimento desta transação?"
                            />
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>