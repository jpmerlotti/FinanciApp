<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            Contas a Pagar (Por Pessoa/Empresa)
        </x-slot>

        @if(empty($counterparties))
            <div class="p-4 text-center text-gray-500 dark:text-gray-400">
                Nenhuma conta a pagar pendente neste período.
            </div>
        @else
            <div class="flex flex-col gap-4">
                @foreach($counterparties as $group)
                    <div x-data="{ open: false }" class="border rounded-lg dark:border-gray-700 overflow-hidden">
                        <!-- Header -->
                        <div @click="open = !open"
                            class="flex items-center justify-between p-4 cursor-pointer bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                            <div class="flex items-center gap-2">
                                <span class="font-medium text-lg">{{ $group['name'] }}</span>
                                @if($group['has_overdue'])
                                    <x-filament::badge color="danger" size="sm">
                                        Vencidas
                                    </x-filament::badge>
                                @endif
                                <span class="text-sm text-gray-500">({{ $group['transactions']->count() }} itens)</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-lg {{ $group['has_overdue'] ? 'text-danger-600' : '' }}">
                                    {{ Number::currency($group['total_cents'] / 100, 'BRL') }}
                                </span>
                                <x-heroicon-m-chevron-down class="w-5 h-5 transition-transform duration-200"
                                    x-bind:class="{ 'rotate-180': open }" />
                            </div>
                        </div>

                        <!-- Body -->
                        <div x-show="open" x-collapse class="border-t dark:border-gray-700 bg-white dark:bg-gray-900">
                            <table class="w-full text-sm text-left">
                                <thead class="text-xs text-gray-500 uppercase bg-gray-50 dark:bg-gray-800">
                                    <tr>
                                        <th class="px-4 py-2">Vencimento</th>
                                        <th class="px-4 py-2">Título</th>
                                        <th class="px-4 py-2">Categoria</th>
                                        <th class="px-4 py-2 text-right">Valor</th>
                                        <th class="px-4 py-2 text-center">Ação</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($group['transactions'] as $transaction)
                                        <tr
                                            class="border-b dark:border-gray-700 last:border-0 hover:bg-gray-50 dark:hover:bg-gray-800">
                                            <td
                                                class="px-4 py-2 {{ $transaction->status === \App\Enums\TransactionStatuses::OVERDUE ? 'text-danger-600 font-bold' : '' }}">
                                                {{ $transaction->due_date?->format('d/m/Y') }}
                                            </td>
                                            <td class="px-4 py-2 font-medium">{{ $transaction->title }}</td>
                                            <td class="px-4 py-2">
                                                @if($transaction->category)
                                                    <x-filament::badge :color="$transaction->category->color">
                                                        {{ $transaction->category->title }}
                                                    </x-filament::badge>
                                                @endif
                                            </td>
                                            <td class="px-4 py-2 text-right">
                                                {{ Number::currency($transaction->amount_cents / 100, 'BRL') }}
                                            </td>
                                            <td class="px-4 py-2 text-center">
                                                <x-filament::link
                                                    :href="\App\Filament\Resources\Transactions\Tables\TransactionsTable::class"
                                                    target="_blank">
                                                    <!-- Ideally link to Edit Action -->
                                                </x-filament::link>
                                                {{-- Link to edit resource would be cleaner if we had the EditUrl, but we are in a
                                                Table view usually.
                                                For now, let's just show status badge or nothing.
                                                --}}
                                                <x-filament::badge :color="$transaction->status->getColor()">
                                                    {{ $transaction->status->getLabel() }}
                                                </x-filament::badge>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>