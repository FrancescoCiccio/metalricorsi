<x-layouts.app :title="__('Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="grid auto-rows-min gap-4 md:grid-cols-3">
            <div class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
               
                <div class="hover:bg-zinc-50 dark:hover:bg-zinc-700 p-5 h-full flex flex-col justify-end">
                    <div>
                        <h2 class="font-semibold mb-8">
                            Ciao e bentrovato
                        </h2>
                    </div>
                    <flux:dropdown position="bottom" align="start">
                        <flux:profile
                            :name="auth()->user()->name"
                            :initials="auth()->user()->initials()"
                            icon-trailing="chevrons-up-down"
                        />

                        <flux:menu class="w-[220px]">
                            <flux:menu.radio.group>
                                <div class="p-0 text-sm font-normal">
                                    <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                        <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                            <span
                                                class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white"
                                            >
                                                {{ auth()->user()->initials() }}
                                            </span>
                                        </span>

                                        <div class="grid flex-1 text-start text-sm leading-tight">
                                            <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                            <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                                        </div>
                                    </div>
                                </div>
                            </flux:menu.radio.group>

                            <flux:menu.separator />

                            <flux:menu.radio.group>
                                <flux:menu.item :href="route('settings.profile')" icon="cog" wire:navigate>{{ __('Settings') }}</flux:menu.item>
                            </flux:menu.radio.group>

                            <flux:menu.separator />

                            <form method="POST" action="{{ route('logout') }}" class="w-full">
                                @csrf
                                <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                                    {{ __('Log Out') }}
                                </flux:menu.item>
                            </form>
                        </flux:menu>
                    </flux:dropdown>
                    
                </div>

            </div>
            <div class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
                <div class="hover:bg-zinc-50 dark:hover:bg-zinc-700 p-5 h-full flex flex-col justify-end">
                    <h1 class="text-5xl font-extrabold mb-6">

                        {{ \App\Models\Download::count() }}

                    </h1>

                    <flux:subheading class="mb-2">
                        Totale Download disponibili sulla piattaforma
                    </flux:subheading>

                    <flux:button :href="route('downloads.index')" variant="primary" class="mt-4 w-max" wire:navigate>
                        Vai i Download
                    </flux:button>
                </div>
            </div>
            <div class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
                <div class="hover:bg-zinc-50 dark:hover:bg-zinc-700 p-5 h-full flex flex-col justify-end">
                    <h1 class="text-5xl font-extrabold mb-6">

                        {{ \App\Models\Course::count() }}

                    </h1>

                    <flux:subheading class="mb-2">
                        Corsi pubblicati sulla piattaforma
                    </flux:subheading>

                    <flux:button :href="route('courses.index')" variant="primary" class="mt-4 w-max" wire:navigate>
                        Vai ai Corsi
                    </flux:button>
                </div>
            </div>
        </div>
        {{-- 
        <div class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
             <x-placeholder-pattern class="absolute inset-0 size-full stroke-gray-900/20 dark:stroke-neutral-100/20" />
         </div>
        --}}
    </div>
</x-layouts.app>
