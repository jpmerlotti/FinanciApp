<x-filament-widgets::widget>
    <x-filament::section>
        <div class="grid gap-6 grid-cols-1 md:grid-cols-2 xl:grid-cols-2">
            @foreach($stats as $stat)
                <div
                    class="flex flex-col gap-2 p-4 bg-white rounded-xl dark:bg-gray-900 border border-gray-200 dark:border-gray-800 shadow-sm relative overflow-hidden group">

                    <div class="flex items-center gap-4">
                        <div class="p-3 rounded-lg flex items-center justify-center shrink-0 
                                            bg-{{ $stat['color'] }}-50 text-{{ $stat['color'] }}-600 
                                            dark:bg-{{ $stat['color'] }}-400/10 dark:text-{{ $stat['color'] }}-400">
                            @svg($stat['icon'], 'w-6 h-6')
                        </div>

                        <div class="flex flex-col">
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                {{ $stat['label'] }}
                            </span>
                            <span class="text-2xl font-bold tracking-tight text-gray-950 dark:text-white">
                                {{ $stat['value'] }}
                            </span>
                        </div>
                    </div>

                    @if($stat['description'])
                        <div
                            class="flex items-center gap-x-1 text-sm text-{{ $stat['color'] }}-600 dark:text-{{ $stat['color'] }}-400 px-1">
                            <span>{{ $stat['description'] }}</span>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </x-filament::section>
</x-filament-widgets::widget>