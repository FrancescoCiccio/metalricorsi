<div class="w-full">
    {{-- Do your work, then step back. --}}
    <div>
        <flux:heading size="xl">
            Raccolta di video
        </flux:heading>
    </div>

    <div class="mt-10 flex gap-x-4 items-start p-2 lg:p-0 flex-wrap lg:flex-nowrap">
        <!-- Sidebar -->
        <div class="w-full md:w-1/4  rounded-lg">

            <flux:heading size="lg" class="mb-4">
                Filtra per Categoria
            </flux:heading>

                        
            <div class="space-y-2 text-gray-700 mb-8">
                @foreach($tags as $tag)
                    @php
                        $tagId = 'tag-' . Str::slug($tag);
                    @endphp
                    
                    <flux:field variant="inline">
                        <flux:checkbox wire:model.live="selectedTags" value="{{ $tag }}" />
                        <flux:label>{{ $tag }}</flux:label>
                        <flux:error name="terms" />
                    </flux:field>


                @endforeach
            </div>

            <flux:button 
                class="w-full">
                Resetta filtri
            </flux:button>
        </div>

        <div class="w-full md:w-3/4">
            <!-- Video List -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-6">
                @foreach($videos as $video)
                    <div 
                        class="border border-zinc-200 hover:border-zinc-200 border-b-zinc-300/80 dark:border-zinc-600 dark:hover:border-zinc-600 shadow-xs  rounded-lg overflow-hidden">
                        <!-- Course Info -->
                        <div class="p-6 flex items-start justify-start flex-col gap-y-2 h-full">
    
                            <flux:heading>
                                {{ $video->title }}
                            </flux:heading>
    
                            <flux:subheading>
                                {{ \Illuminate\Support\Str::limit(strip_tags($video->description), 120, ' ...') }}    
                            </flux:subheading>

                            <iframe
                                class="w-full aspect-video rounded-md" 
                                src="{{ $video->video_path }}" frameborder="0"></iframe>
    
                            <div class="grow"></div>                        
                        </div>
                    </div>
                @endforeach
            </div>
        </div>


    </div>
</div>
