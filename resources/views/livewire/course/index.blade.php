<div class="w-full">
    <div class="">
        
        <flux:heading size="xl">I Corsi di Metal.Ri</flux:heading>

        <flux:subheading size="lg" class="mt-2 max-w-2xl">
            Metal.Ri Academy nasce con l’obiettivo di promuovere la diffusione delle nuove tecnologie costruttive nell’ottica della prefabbricazione edilizia e dell’industrializzazione del cantiere, organizzando webinar, seminari e convegni finalizzati all’aggiornamento continuo delle conoscenze professionali, scientifiche e tecniche di coloro i quali operano nel campo delle costruzioni.
        </flux:subheading>
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

        <!-- Main Content -->
        <div class="w-full md:w-3/4 mt-4 lg:mt-0">
            <!-- Search Input -->
            <div class="mb-4 flex justify-end">
                <flux:input wire:model.live.debounce.300="search" kbd="⌘K" icon="magnifying-glass" placeholder="Cerca corsi..."/> 
            </div>

            <!-- Courses List -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($courses as $course)
                    <div 
                        class="border border-zinc-200 hover:border-zinc-200 border-b-zinc-300/80 dark:border-zinc-600 dark:hover:border-zinc-600 shadow-xs  rounded-lg overflow-hidden">
                        <!-- Course Info -->
                        <div class="p-6 flex items-start justify-start flex-col gap-y-2 h-full">
                            
                            <img 
                                src="/storage/{{ $course->miniature_url }}" 
                                class="rounded-md bg-gray-50 max-h-32 w-full object-cover" 
                                alt="">

                            <span class="bg-slate-800 text-white text-sm font-medium px-5 rounded-md py-1 ">
                                {{ $course->location }}
                            </span>

                            <flux:heading>
                                {{ $course->title }}
                            </flux:heading>

                            <flux:subheading>
                                {{ \Illuminate\Support\Str::limit(strip_tags($course->description), 120, ' ...') }}    
                            </flux:subheading>

                            <div class="grow"></div>

                            <p class="flex gap-x-1 items-center text-sm text-slate-400 font-light leading-snug">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z" class="stroke-current" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M12 6V12L16 14" class="stroke-current" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>                                
                                <span>
                                    {{ $course->when->format('D M Y') }}
                                </span>
                            </p>

                            <!-- Buttons -->
                            <div class="flex flex-col w-full gap-y-2 items-center gap-x-2 justify-between">

                                @unless(now()->isAfter($course->when))

                                    @if(Auth::check() && !in_array($course->id, $subscribedCourseIds))
                                        <flux:button 
                                            wire:click="subscribe({{ $course->id }})"
                                            variant="primary" class="w-full cursor-pointer">
                                            Iscriviti
                                        </flux:button>
                                    @else()
                                        <flux:button 
                                            disabled
                                            variant="primary" class="w-full cursor-not-allowed">
                                            Iscritto
                                        </flux:button>                                
                                    @endif
                                    
                                @endunless
                                
                                <flux:button href="{{ route('courses.show', $course) }}" class="w-full">Approfondisci</flux:button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-6">
                {{ $courses->links() }}
            </div>
        </div>
    </div>
</div>
