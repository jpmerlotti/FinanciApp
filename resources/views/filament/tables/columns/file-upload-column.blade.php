<div class="fi-ta-text-input px-3 py-4" x-data='{
        state: @json($getState()),
        isLoading: false,
        files: null,
        
        async uploadFile(event) {
            const file = event.target.files[0];
            if (!file) return;

            this.isLoading = true;
            
            $wire.mountTableAction("upload_file", @json($recordKey));
        }
    }'>

    <div @click="$wire.mountTableAction('upload_file', @json($recordKey))"
        class="cursor-pointer flex items-center gap-2 text-sm text-gray-500 hover:text-primary-500 transition">
        @if($getState())
            <x-heroicon-m-document-text class="w-5 h-5 text-gray-400" />
            <span
                class="truncate max-w-[10rem] underline decoration-gray-300 underline-offset-2">{{ Str::limit($getState(), 20) }}</span>
        @else
            <x-heroicon-m-paper-clip class="w-4 h-4" />
            <span class="text-xs">Anexar</span>
        @endif
    </div>
</div>