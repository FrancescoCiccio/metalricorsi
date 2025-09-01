

<x-layouts.app :title="__('Dashboard')">
    <div class="">
        <div class="mb-12" id="bread">
            <flux:button 
                href="{{ route('courses.index') }}"
                icon="arrow-long-left">Torna indietro</flux:button>
        </div>
        
    
        <div class="flex flex-wrap">
            <div class="w-full lg:w-1/2 flex flex-col gap-y-4">

                <flux:heading size="xl">
                    {{ $course->title }}
                </flux:heading>

                <flux:subheading>
                    {!! $course->description !!}
                </flux:subheading>
    
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
    
                    <button
                        class="bg-slate-800 hover:bg-slate-700 transition-all duration-150 text-white text-base font-medium px-5 py-2 rounded-md flex gap-x-4 items-center"
                                    >
    
                                    <svg class="w-4 h-4" viewBox="0 0 13 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M11.8334 1L4.50008 8.33333L1.16675 5" class="stroke-current" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                        
    
                                    <span>
                                        Iscriviti
                                    </span>
                    </button>
    
                    {{-- <a  
                        href="{{ route('courses.show', $course) }}" 
                        class="border-slate-200 hover:bg-white transition-all duration-150 flex items-center border border-solid text-slate-800 px-5 font-semibold py-2 rounded-lg gap-x-4">
                        <svg 
                            class="w-4 h-4" 
                            viewBox="0 0 16 16" 
                            fill="none" 
                            xmlns="http://www.w3.org/2000/svg">
                            <g clip-path="url(#clip0_1133_373)">
                            <path 
                            d="M7.99992 14.6667C11.6818 14.6667 14.6666 11.6819 14.6666 8C14.6666 4.3181 11.6818 1.33333 7.99992 1.33333C4.31802 1.33333 1.33325 4.3181 1.33325 8C1.33325 11.6819 4.31802 14.6667 7.99992 14.6667Z" class="stroke-current" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M6.66675 5.33333L10.6667 7.99999L6.66675 10.6667V5.33333Z" class="stroke-current" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </g>
                            <defs>
                            <clipPath id="clip0_1133_373">
                            <rect width="16" height="16" fill="white"/>
                            </clipPath>
                            </defs>
                            </svg>                            
                        <span>
                            Guarda il video
                        </span>
                    </a> --}}
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
    </div>

</x-layouts.app>
