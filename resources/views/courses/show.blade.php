

<x-layouts.app :title="__('Dashboard')">
    <div class="">
        <div class="mb-12" id="bread">
            <flux:button 
                href="{{ route('courses.index') }}"
                icon="arrow-long-left">Torna indietro</flux:button>
        </div>
        
    
        <div class="flex flex-wrap">
            <div class="w-full lg:w-1/2 flex flex-col gap-y-4 prose">

                <flux:heading size="xl">
                    {{ $course->title }}
                </flux:heading>

                <div class="prose max-w-none">
                    {!! $course->description !!}
                </div>
    
                <div class="bg-white flex gap-x-4 rounded-lg border-slate-200 border-2 p-6">
                    <div class="flex gap-x-2 items-center text-slate-500">
                        <span>
                            {{ $course->when->format("D M Y H:i")}}
                        </span>
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z" class="stroke-current" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M12 6V12L16 14" class="stroke-current" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>        
                    </div>
                </div>
    
                <div class="flex gap-x-4">
                    @unless(now()->isAfter($course->when))
                        @if(Auth::check() && !$isSubscribed)
                            <a
                                href="{{ route('courses.subscribe', $course) }}"
                                class="bg-slate-800 hover:bg-slate-700 transition-all duration-150 text-white text-base font-medium px-5 py-2 rounded-md flex gap-x-4 items-center"
                                >
                                <svg class="w-4 h-4" viewBox="0 0 13 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M11.8334 1L4.50008 8.33333L1.16675 5" class="stroke-current" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                    
                                <span>
                                    Iscriviti
                                </span>
                            </a>
                        @else
                            <div
                                class="w-full bg-green-50 border-green-100 rounded-lg border-solid border p-2 text-sm font-semibold text-green-700 flex gap-x-2 items-center"
                                >
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-party-popper-icon lucide-party-popper"><path d="M5.8 11.3 2 22l10.7-3.79"/><path d="M4 3h.01"/><path d="M22 8h.01"/><path d="M15 2h.01"/><path d="M22 20h.01"/><path d="m22 2-2.24.75a2.9 2.9 0 0 0-1.96 3.12c.1.86-.57 1.63-1.45 1.63h-.38c-.86 0-1.6.6-1.76 1.44L14 10"/><path d="m22 13-.82-.33c-.86-.34-1.82.2-1.98 1.11c-.11.7-.72 1.22-1.43 1.22H17"/><path d="m11 2 .33.82c.34.86-.2 1.82-1.11 1.98C9.52 4.9 9 5.52 9 6.23V7"/><path d="M11 13c1.93 1.93 2.83 4.17 2 5-.83.83-3.07-.07-5-2-1.93-1.93-2.83-4.17-2-5 .83-.83 3.07.07 5 2Z"/></svg>
                                Risulti già iscritto a questo webinar
                            </div>
                        @endif
                    @endunless
                </div>
            </div>
    
            <div class="lg:w-1/2 p-4">
                @if($course->cover_path)
                    <img src="/storage/{{ $course->cover_path }}" alt="{{ $course->title }}" class="max-w-[80%] object-cover">
                @else
                    <div class="w-full h-48 bg-gray-200 flex items-center justify-center">
                        <span class="text-gray-500">No Image</span>
                    </div>
                @endif

                @if ($course->youtube_embed)
                    <div class="mt-4">
                        <iframe class="aspect-video" width="100%" src="{{ $course->youtube_embed }}" frameborder="0"></iframe>
                    </div>
                @endif
            </div>
        </div>

        @if ($course->relators && count($course->relators) > 0)
            <div class="bg-gray-100 dark:bg-gray-800 p-6 rounded-lg mt-10">
                <flux:heading size="lg" class="mb-4">
                    Relatori
                </flux:heading>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($course->relators as $relator)
                        <div class="bg-white dark:bg-gray-700 p-4 rounded-lg ">
                            <img src="/storage/{{ $relator['photo'] }}" alt="{{ $relator['name'] }}" class="w-full rounded-md">
                            <h3 class="text-xl font-semibold mb-2">{{ $relator['name'] }}</h3>
                            @if (!empty($relator['bio']))
                                <p class="text-gray-600 dark:text-gray-300">{{ $relator['bio'] }}</p>
                            @else
                                <p class="text-gray-600 dark:text-gray-300">Nessuna biografia disponibile.</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        @if ($course->additional_resources)

            <div class="bg-gray-100 dark:bg-gray-800 p-6 rounded-lg mt-10">
                <flux:heading size="lg" class="mb-4">
                    Risorse Aggiuntive
                </flux:heading>

                <div class="flex gap-x-4 flex-wrap">
                    {{-- Loop through resources --}}
                    @foreach ($course->additional_resources as $resource)
                        <flux:button 
                            href="/storage/{{ $resource['file_path'] }}"
                            icon="arrow-down-on-square"
                            target="_blank"
                            download
                            >
                            {{ $resource['name'] }}
                        </flux:button>
                    @endforeach
                </div>
            </div>
        @endif

    </div>

</x-layouts.app>
