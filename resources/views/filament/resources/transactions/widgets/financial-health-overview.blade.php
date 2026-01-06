<x-filament::widget>
    <div class="grid gap-6 md:grid-cols-3">

        <div class="flex flex-col justify-between rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div>
                <div class="mb-4 flex items-center gap-2 rounded-lg bg-gray-50 px-3 py-2 dark:bg-gray-800">
                    <x-filament::icon icon="heroicon-m-scale" class="h-5 w-5 text-gray-500 dark:text-gray-400" />
                    <h3 class="text-sm font-bold text-gray-700 dark:text-gray-200">Balanço Real (Caixa)</h3>
                </div>

                <div class="mt-4 flex items-center gap-3">
                    <span class="text-4xl font-extrabold tracking-tight {{ $balance['is_positive'] ? 'text-primary-500' : 'text-danger-500' }}">
                        {{ $balance['is_positive'] ? '+' : '' }} R$ {{ $balance['real'] }}
                    </span>

                    @if($balance['is_positive'])
                        <div class="rounded-full bg-primary-100 p-1 dark:bg-primary-900/30">
                            <x-filament::icon icon="heroicon-m-arrow-trending-up" class="h-6 w-6 text-primary-600 dark:text-primary-400" />
                        </div>
                    @else
                        <div class="rounded-full bg-danger-100 p-1 dark:bg-danger-900/30">
                            <x-filament::icon icon="heroicon-m-arrow-trending-down" class="h-6 w-6 text-danger-600 dark:text-danger-400" />
                        </div>
                    @endif
                </div>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                    Saldo disponível considerando apenas o efetivamente pago e recebido.
                </p>
            </div>

            <div class="mb-6 flex flex-col gap-2">

                <div class="">
                    <div class="pt-2 text-lg font-semibold text-gray-400 dark:text-gray-500">
                        R$ {{ $balance['estimated'] }}
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">
                        Previsão (Caixa)
                    </div>
                </div>
                <div class="flex items-center gap-2">

                    <div class="h-2 flex-1 rounded-full bg-gray-100 dark:bg-gray-800">
                        <div class="h-full w-1/2 rounded-full {{ $balance['is_positive'] ? 'bg-primary-500' : 'bg-danger-500' }}"></div>
                    </div>
                    <span class="text-xs text-gray-400">Hoje</span>
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="mb-4 flex items-center gap-2 rounded-lg bg-primary-50 px-3 py-2 dark:bg-primary-900/20">
                <x-filament::icon icon="heroicon-m-arrow-down-circle" class="h-5 w-5 text-primary-600 dark:text-primary-400" />
                <h3 class="text-sm font-bold text-primary-700 dark:text-primary-400">Entradas/Receitas</h3>
            </div>

            <div class="mb-6 space-y-1">
                <div class="text-2xl font-bold text-gray-900 dark:text-white">
                    R$ {{ $income['paid'] }}
                </div>
                <div class="text-xs text-gray-500 dark:text-gray-400">
                    Realizado (Caixa)
                </div>

                <div class="pt-2 text-lg font-semibold text-gray-400 dark:text-gray-500">
                    R$ {{ $income['pending'] }}
                </div>
                <div class="text-xs text-gray-500 dark:text-gray-400">
                    Pendente (A receber)
                </div>
            </div>

            <div class="relative h-2 w-full overflow-hidden rounded-full bg-gray-100 dark:bg-gray-700">
                <div class="absolute left-0 top-0 h-full bg-primary-500 transition-all duration-1000"
                    style="width: {{ $income['paid_pct'] }}%"></div>
            </div>
            <div class="mt-2 flex justify-between text-xs">
                <span class="font-medium text-primary-600 dark:text-primary-400">{{ $income['paid_pct'] }}% Recebido</span>
                <span class="text-gray-400">Previsto: R$ {{ $income['total'] }}</span>
            </div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="mb-4 flex items-center gap-2 rounded-lg bg-danger-50 px-3 py-2 dark:bg-danger-900/20">
                <x-filament::icon icon="heroicon-m-arrow-up-circle" class="h-5 w-5 text-danger-600 dark:text-danger-400" />
                <h3 class="text-sm font-bold text-danger-700 dark:text-danger-400">Saídas/Despesas</h3>
            </div>

            <div class="mb-6 space-y-1">
                <div class="text-2xl font-bold text-gray-900 dark:text-white">
                    R$ {{ $expense['paid'] }}
                </div>
                <div class="text-xs text-gray-500 dark:text-gray-400">
                    Pago (Realizado)
                </div>

                <div class="pt-2 text-lg font-semibold text-gray-400 dark:text-gray-500">
                    R$ {{ $expense['pending'] }}
                </div>
                <div class="text-xs text-gray-500 dark:text-gray-400">
                    A Pagar
                </div>
            </div>

            <div class="relative h-2 w-full overflow-hidden rounded-full bg-gray-100 dark:bg-gray-700">
                <div class="absolute left-0 top-0 h-full bg-danger-500 transition-all duration-1000"
                     style="width: {{ $expense['paid_pct'] }}%"></div>
            </div>
            <div class="mt-2 flex justify-between text-xs">
                <span class="font-medium text-danger-600 dark:text-danger-400">{{ $expense['paid_pct'] }}% Pago</span>
                <span class="text-gray-400">Previsto: R$ {{ $expense['total'] }}</span>
            </div>
        </div>
    </div>
</x-filament::widget>
