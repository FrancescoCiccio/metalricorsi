<flux:dropdown position="top" align="start">
    <flux:button variant="subtle" size="sm" icon="language" aria-label="{{ __('Cambia lingua') }}">
        {{ strtoupper(app()->getLocale()) }}
    </flux:button>

    <flux:menu>
        @foreach (['it' => 'Italiano', 'en' => 'English', 'fr' => 'Français'] as $locale => $label)
            <form method="POST" action="{{ route('language.update', $locale) }}">
                @csrf
                <flux:menu.item as="button" type="submit" :disabled="app()->getLocale() === $locale">
                    {{ $label }}
                </flux:menu.item>
            </form>
        @endforeach
    </flux:menu>
</flux:dropdown>
