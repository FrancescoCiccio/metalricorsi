<div class="w-full">

    <div>
        <flux:heading size="xl">
             I tuoi materiali disponibili al download
        </flux:heading>

        <flux:subheading size="lg" class="mt-2 max-w-2xl">
            In questa sezione trovi tutti i materiali a te riservati: dispense, guide, risorse extra. Puoi filtrare per categoria per trovare più velocemente ciò che ti serve.        
        </flux:subheading>
    </div>

    <div class="w-full flex flex-col lg:flex-row space-2 gap-4 mt-10">

        {{-- Sidebar --}}
        <div class="w-full md:w-1/4 rounded-lg">
            <flux:heading size="lg" class="mb-4">
                Filtra per Categoria
            </flux:heading>

            <div class="space-y-2 text-gray-700 mb-8">
                @foreach($allTags as $tag)
                    @php
                        $tagId = 'tag-' . Str::slug($tag);
                    @endphp

                    <flux:field variant="inline">
                        <flux:checkbox wire:model.live="selectedTags" value="{{ $tag }}" />
                        <flux:label>{{ $tag }}</flux:label>
                        <flux:error name="selectedTags" />
                    </flux:field>
                @endforeach
            </div>

            <flux:button wire:click="resetFilters" class="w-full">
                Resetta filtri
            </flux:button>
        </div>
        {{-- END Sidebar --}}

        {{-- Main Content --}}
        <div class="w-full md:w-3/4 mt-4 lg:mt-0">
            @if($downloads->isEmpty())
                <p class="text-gray-600 text-sm">Nessun download disponibile.</p>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
                    @foreach($downloads as $download)
                        <div class="border border-zinc-200 hover:border-zinc-200 border-b-zinc-300/80 dark:border-zinc-600 dark:hover:border-zinc-600 shadow-xs  rounded-lg overflow-hidden">
                            <div class="p-6 flex items-start justify-start gap-y-2 h-full flex-col">
                                <div class="flex flex-col gap-y-2">
                                    <div class="flex flex-wrap gap-1 mb-3">
                                        @foreach($download->tags as $tag)
                                            <span class="bg-slate-800 text-white text-sm font-medium px-5 rounded-md py-1">{{ $tag->name }}</span>
                                        @endforeach
                                    </div>
                                    <div class="flex flex-col gap-y-1">
                                        <h3 class="text-lg font-semibold mb-1">{{ $download->title }}</h3>
                                        <flux:subheading>
                                            {!! $download->description !!}
                                        </flux:subheading>
                                    </div>
                                </div>

                                <flux:button 
                                    variant="primary"
                                    icon="arrow-down-tray"
                                    href="/storage/{{ $download->file_path }}" target="_blank" class="w-full">Scarica</flux:button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
        {{-- END Main Content --}}
    </div>

    

</div>