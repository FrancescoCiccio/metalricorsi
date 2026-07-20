# Piattaforma Multilingua (IT/EN/FR) — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Rendere la piattaforma Metal.Ri Academy multilingua (italiano principale, inglese, francese): interfaccia, email e contenuti DB gestibili da Filament.

**Architecture:** Contenuti tradotti come colonne JSON via `spatie/laravel-translatable` con plugin ufficiale Filament per l'editing per-lingua. Lingua scelta via preferenza utente/sessione/browser con middleware `SetLocale`; interfaccia tradotta con file JSON `lang/{it,en,fr}.json`. Fallback sempre italiano.

**Tech Stack:** Laravel 12, PHP 8.2, Livewire 3 + Flux + Volt, Filament 3.3, Pest, sqlite (dev/test).

**Spec:** `docs/superpowers/specs/2026-07-20-multilingua-design.md`

## Global Constraints

- Locali supportati: `it`, `en`, `fr`. Default e fallback: `it`.
- Fallback contenuti: traduzione mancante → si mostra l'italiano; nessun contenuto nascosto.
- Campi traducibili: solo `title` e `description` di Course, Video, Download (più i Tag, già JSON). `relators`, `additional_resources`, `location`, `groups` NON si traducono (v1).
- Search: lingua corrente + italiano.
- Convenzione chiavi di traduzione: il testo naturale è la chiave (es. `__('Iscriviti')`), coerente con l'esistente.
- Admin Filament resta in italiano come interfaccia.
- Test: Pest, `RefreshDatabase` già attivo su `tests/Feature` via `tests/Pest.php`.
- Comando test: `php artisan test --filter=<nome>` (o `./vendor/bin/pest`).
- Commit frequenti, messaggi in stile esistente (imperativo breve).

---

### Task 1: Pacchetti e configurazione locale

**Files:**
- Modify: `composer.json` (via composer require)
- Modify: `.env`, `.env.example`, `phpunit.xml`

**Interfaces:**
- Produces: pacchetti `spatie/laravel-translatable` e `filament/spatie-laravel-translatable-plugin` installati; `config('app.locale') === 'it'` e `config('app.fallback_locale') === 'it'` ovunque, test inclusi.

- [ ] **Step 1: Installa i pacchetti**

```bash
composer require spatie/laravel-translatable filament/spatie-laravel-translatable-plugin
```

Expected: entrambi installati senza conflitti (Filament 3.3 richiede plugin ^3.x).

- [ ] **Step 2: Configura i locale in .env e .env.example**

In `.env` (la riga `APP_LOCALE=it` esiste già) aggiungi/verifica:

```dotenv
APP_LOCALE=it
APP_FALLBACK_LOCALE=it
```

Stessa cosa in `.env.example`.

- [ ] **Step 3: Rendi deterministici i test**

In `phpunit.xml`, dentro `<php>`, aggiungi:

```xml
<env name="APP_LOCALE" value="it"/>
<env name="APP_FALLBACK_LOCALE" value="it"/>
```

- [ ] **Step 4: Verifica**

```bash
php artisan config:clear && php artisan tinker --execute="echo config('app.locale').' '.config('app.fallback_locale');"
```

Expected: `it it`

- [ ] **Step 5: Commit**

```bash
git add composer.json composer.lock .env.example phpunit.xml
git commit -m "Add translatable packages and locale config"
```

---

### Task 2: Colonna `users.locale` e `preferredLocale()`

**Files:**
- Create: `database/migrations/2026_07_20_000001_add_locale_to_users_table.php`
- Modify: `app/Models/User.php`
- Test: `tests/Feature/LocalizationTest.php` (nuovo)

**Interfaces:**
- Produces: `User::$locale` (string|null, fillable), `User::preferredLocale(): string` (ritorna `locale` o `'it'`). `User` implementa `Illuminate\Contracts\Translation\HasLocalePreference` — usato dal mail channel per localizzare le notifiche.

- [ ] **Step 1: Scrivi il test che fallisce**

Crea `tests/Feature/LocalizationTest.php`:

```php
<?php

use App\Models\User;

test('preferredLocale ritorna it quando l utente non ha scelto una lingua', function () {
    $user = User::factory()->create();

    expect($user->preferredLocale())->toBe('it');
});

test('preferredLocale ritorna la lingua scelta dall utente', function () {
    $user = User::factory()->create(['locale' => 'fr']);

    expect($user->preferredLocale())->toBe('fr');
});
```

- [ ] **Step 2: Esegui il test e verifica che fallisca**

Run: `php artisan test --filter=preferredLocale`
Expected: FAIL (colonna `locale` inesistente / metodo non definito)

- [ ] **Step 3: Crea la migration**

`database/migrations/2026_07_20_000001_add_locale_to_users_table.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('locale', 5)->nullable()->after('email');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('locale');
        });
    }
};
```

Nota: nullable senza default — così per chi non ha mai scelto vale la catena sessione → browser (Task 3), e `preferredLocale()` fa fallback a `it`.

- [ ] **Step 4: Aggiorna il modello User**

In `app/Models/User.php`:

1. Aggiungi gli import:

```php
use Illuminate\Contracts\Translation\HasLocalePreference;
```

2. Cambia la firma della classe:

```php
class User extends Authenticatable implements FilamentUser, CanResetPassword, HasLocalePreference
```

3. Aggiungi `'locale'` a `$fillable`:

```php
protected $fillable = [
    'name',
    'email',
    'password',
    'locale',
];
```

4. Aggiungi il metodo:

```php
/**
 * The user's preferred locale, used to localize notifications.
 */
public function preferredLocale(): string
{
    return $this->locale ?? config('app.locale');
}
```

- [ ] **Step 5: Esegui migrate e test**

Run: `php artisan migrate && php artisan test --filter=preferredLocale`
Expected: PASS (2 tests)

- [ ] **Step 6: Commit**

```bash
git add database/migrations/2026_07_20_000001_add_locale_to_users_table.php app/Models/User.php tests/Feature/LocalizationTest.php
git commit -m "Add locale preference to users"
```

---

### Task 3: Middleware `SetLocale`

**Files:**
- Create: `app/Http/Middleware/SetLocale.php`
- Modify: `bootstrap/app.php`
- Test: `tests/Feature/LocalizationTest.php`

**Interfaces:**
- Consumes: `User::$locale` (Task 2).
- Produces: `App\Http\Middleware\SetLocale` con costante pubblica `SetLocale::SUPPORTED = ['it', 'en', 'fr']` (usata da Task 4 e 6). Registrato in append al gruppo `web`: ogni richiesta (Livewire incluso) ha `app()->getLocale()` corretto.

- [ ] **Step 1: Scrivi i test che falliscono**

Aggiungi a `tests/Feature/LocalizationTest.php`:

```php
test('la lingua preferita dell utente autenticato viene applicata', function () {
    $user = User::factory()->create(['locale' => 'en']);

    $this->actingAs($user)->get('/dashboard');

    expect(app()->getLocale())->toBe('en');
});

test('la lingua in sessione viene applicata agli ospiti', function () {
    $this->withSession(['locale' => 'fr'])->get('/login');

    expect(app()->getLocale())->toBe('fr');
});

test('la lingua del browser viene usata senza preferenze salvate', function () {
    $this->get('/login', ['Accept-Language' => 'fr-FR,fr;q=0.9,en;q=0.5']);

    expect(app()->getLocale())->toBe('fr');
});

test('una lingua non supportata in sessione ricade su it', function () {
    $this->withSession(['locale' => 'de'])->get('/login');

    expect(app()->getLocale())->toBe('it');
});
```

- [ ] **Step 2: Esegui i test e verifica che falliscano**

Run: `php artisan test --filter=lingua`
Expected: FAIL sui casi `en`/`fr` (locale resta `it`)

- [ ] **Step 3: Crea il middleware**

`app/Http/Middleware/SetLocale.php`:

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * The locales supported by the platform.
     *
     * @var list<string>
     */
    public const SUPPORTED = ['it', 'en', 'fr'];

    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->user()?->locale
            ?? $request->session()->get('locale')
            ?? $request->getPreferredLanguage(self::SUPPORTED);

        if (! in_array($locale, self::SUPPORTED, true)) {
            $locale = config('app.locale');
        }

        app()->setLocale($locale);

        return $next($request);
    }
}
```

Nota: `getPreferredLanguage(['it', 'en', 'fr'])` ritorna `it` (primo elemento) quando l'header non matcha nulla.

- [ ] **Step 4: Registra il middleware**

In `bootstrap/app.php` sostituisci il blocco `withMiddleware`:

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->web(append: [
        \App\Http\Middleware\SetLocale::class,
    ]);
})
```

- [ ] **Step 5: Esegui i test**

Run: `php artisan test --filter=lingua`
Expected: PASS (4 tests)

- [ ] **Step 6: Esegui l'intera suite per regressioni**

Run: `php artisan test`
Expected: tutti PASS

- [ ] **Step 7: Commit**

```bash
git add app/Http/Middleware/SetLocale.php bootstrap/app.php tests/Feature/LocalizationTest.php
git commit -m "Add SetLocale middleware"
```

---

### Task 4: Rotta cambio lingua

**Files:**
- Modify: `routes/web.php`
- Test: `tests/Feature/LocalizationTest.php`

**Interfaces:**
- Consumes: `SetLocale::SUPPORTED` (Task 3).
- Produces: rotta `POST /language/{locale}` con nome `language.update`: salva in sessione, persiste su `users.locale` se autenticato, redirect back. Usata dallo switcher (Task 5).

- [ ] **Step 1: Scrivi i test che falliscono**

Aggiungi a `tests/Feature/LocalizationTest.php`:

```php
test('il cambio lingua viene salvato in sessione per gli ospiti', function () {
    $this->from('/login')->post('/language/en')->assertRedirect('/login');

    expect(session('locale'))->toBe('en');
});

test('il cambio lingua viene salvato sull utente autenticato', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post('/language/fr');

    expect($user->fresh()->locale)->toBe('fr')
        ->and(session('locale'))->toBe('fr');
});

test('una lingua non supportata restituisce 404', function () {
    $this->post('/language/de')->assertNotFound();
});
```

- [ ] **Step 2: Esegui i test e verifica che falliscano**

Run: `php artisan test --filter="cambio lingua"`
Expected: FAIL (404 sulla rotta)

- [ ] **Step 3: Aggiungi la rotta**

In `routes/web.php`, dopo gli `use` esistenti aggiungi:

```php
use App\Http\Middleware\SetLocale;
```

e dopo la rotta `home`:

```php
Route::post('language/{locale}', function (string $locale) {
    abort_unless(in_array($locale, SetLocale::SUPPORTED, true), 404);

    session(['locale' => $locale]);

    request()->user()?->forceFill(['locale' => $locale])->save();

    return back();
})->name('language.update');
```

- [ ] **Step 4: Esegui i test**

Run: `php artisan test --filter="cambio lingua" && php artisan test --filter="non supportata"`
Expected: PASS

- [ ] **Step 5: Commit**

```bash
git add routes/web.php tests/Feature/LocalizationTest.php
git commit -m "Add language switch route"
```

---

### Task 5: Switcher lingua in sidebar e pagine auth

**Files:**
- Create: `resources/views/components/language-switcher.blade.php`
- Modify: `resources/views/components/layouts/app/sidebar.blade.php`
- Modify: `resources/views/components/layouts/auth/split.blade.php`
- Test: `tests/Feature/LocalizationTest.php`

**Interfaces:**
- Consumes: rotta `language.update` (Task 4).
- Produces: componente Blade `<x-language-switcher />` riutilizzabile.

- [ ] **Step 1: Scrivi i test che falliscono**

Aggiungi a `tests/Feature/LocalizationTest.php`:

```php
test('la sidebar mostra lo switcher lingua', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/dashboard')
        ->assertSee('Italiano')
        ->assertSee('English')
        ->assertSee('Français');
});

test('la pagina di login mostra lo switcher lingua', function () {
    $this->get('/login')
        ->assertSee('Italiano')
        ->assertSee('English')
        ->assertSee('Français');
});
```

- [ ] **Step 2: Esegui i test e verifica che falliscano**

Run: `php artisan test --filter=switcher`
Expected: FAIL

- [ ] **Step 3: Crea il componente**

`resources/views/components/language-switcher.blade.php`:

```blade
<flux:dropdown position="top" align="start">
    <flux:button variant="subtle" size="sm" icon="language">
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
```

- [ ] **Step 4: Includi il componente nei layout**

In `resources/views/components/layouts/app/sidebar.blade.php`, subito dopo `<flux:spacer />`:

```blade
<x-language-switcher />
```

In `resources/views/components/layouts/auth/split.blade.php`, individua il contenitore del form (la colonna con `{{ $slot }}`) e aggiungi prima dello slot:

```blade
<div class="flex justify-end">
    <x-language-switcher />
</div>
```

- [ ] **Step 5: Esegui i test**

Run: `php artisan test --filter=switcher`
Expected: PASS (2 tests)

- [ ] **Step 6: Verifica visiva**

Run: `php artisan serve` e apri `/login` e `/dashboard`: il dropdown IT/EN/FR è visibile e cliccando una lingua la pagina si ricarica.

- [ ] **Step 7: Commit**

```bash
git add resources/views/components/language-switcher.blade.php resources/views/components/layouts/app/sidebar.blade.php resources/views/components/layouts/auth/split.blade.php tests/Feature/LocalizationTest.php
git commit -m "Add language switcher to sidebar and auth layout"
```

---

### Task 6: Campo Lingua nelle impostazioni profilo

**Files:**
- Modify: `resources/views/livewire/settings/profile.blade.php`
- Test: `tests/Feature/Settings/ProfileUpdateTest.php`

**Interfaces:**
- Consumes: `User::$locale` fillable (Task 2), `SetLocale::SUPPORTED` (Task 3).
- Produces: proprietà `$locale` sul componente Volt `settings.profile`, salvata con il profilo.

- [ ] **Step 1: Scrivi il test che fallisce**

Aggiungi a `tests/Feature/Settings/ProfileUpdateTest.php` (usa lo stesso stile Volt dei test esistenti nel file):

```php
test('user can update locale from profile settings', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Volt::test('settings.profile')
        ->set('name', $user->name)
        ->set('email', $user->email)
        ->set('locale', 'en')
        ->call('updateProfileInformation');

    expect($user->fresh()->locale)->toBe('en')
        ->and(session('locale'))->toBe('en');
});
```

- [ ] **Step 2: Esegui il test e verifica che fallisca**

Run: `php artisan test --filter="update locale from profile"`
Expected: FAIL (property `$locale` inesistente)

- [ ] **Step 3: Aggiorna il componente Volt**

In `resources/views/livewire/settings/profile.blade.php`:

1. Aggiungi l'import in cima:

```php
use App\Http\Middleware\SetLocale;
```

2. Aggiungi la proprietà accanto a `$name`/`$email`:

```php
public string $locale = 'it';
```

3. In `mount()` aggiungi:

```php
$this->locale = Auth::user()->locale ?? app()->getLocale();
```

4. In `updateProfileInformation()`, aggiungi alla validazione:

```php
'locale' => ['required', 'string', Rule::in(SetLocale::SUPPORTED)],
```

e dopo `$user->save();` aggiungi:

```php
session(['locale' => $this->locale]);
```

(`$user->fill($validated)` include già `locale` perché fillable.)

5. Nel form Blade, dopo il blocco email aggiungi:

```blade
<flux:select wire:model="locale" :label="__('Lingua')">
    <flux:select.option value="it">Italiano</flux:select.option>
    <flux:select.option value="en">English</flux:select.option>
    <flux:select.option value="fr">Français</flux:select.option>
</flux:select>
```

- [ ] **Step 4: Esegui i test**

Run: `php artisan test --filter=ProfileUpdate`
Expected: PASS (incluso il nuovo test; i test esistenti non si rompono perché `$locale` ha default `'it'`)

- [ ] **Step 5: Commit**

```bash
git add resources/views/livewire/settings/profile.blade.php tests/Feature/Settings/ProfileUpdateTest.php
git commit -m "Add language field to profile settings"
```

---

### Task 7: Modelli traducibili e conversione dati

**Files:**
- Create: `database/migrations/2026_07_20_000002_convert_content_to_translatable.php`
- Modify: `app/Models/Course.php`, `app/Models/Video.php`, `app/Models/Download.php`
- Test: `tests/Feature/TranslatableContentTest.php` (nuovo)

**Interfaces:**
- Consumes: pacchetto `spatie/laravel-translatable` (Task 1).
- Produces: `Course`, `Video`, `Download` con trait `Spatie\Translatable\HasTranslations` e `public array $translatable = ['title', 'description']`. Accesso: `$course->title` ritorna la lingua corrente con fallback it; `$course->setTranslation('title', 'en', '...')` / creazione con array `['title' => ['it' => '...', 'en' => '...']]`.

- [ ] **Step 1: Scrivi i test che falliscono**

Crea `tests/Feature/TranslatableContentTest.php`:

```php
<?php

use App\Models\Course;
use App\Models\Download;
use App\Models\Video;

test('un contenuto tradotto mostra la lingua corrente', function () {
    $course = Course::create([
        'title' => ['it' => 'Corso saldatura', 'en' => 'Welding course'],
        'description' => ['it' => 'Descrizione', 'en' => 'Description'],
        'when' => now()->addDay(),
    ]);

    app()->setLocale('en');

    expect($course->title)->toBe('Welding course');
});

test('un contenuto senza traduzione fa fallback sull italiano', function () {
    $course = Course::create([
        'title' => ['it' => 'Corso saldatura'],
        'when' => now()->addDay(),
    ]);

    app()->setLocale('en');

    expect($course->title)->toBe('Corso saldatura');
});

test('video e download sono traducibili con fallback', function () {
    $video = Video::create(['title' => ['it' => 'Video ita']]);
    $download = Download::create(['title' => ['it' => 'Dispensa'], 'file_path' => 'x.pdf']);

    app()->setLocale('fr');

    expect($video->title)->toBe('Video ita')
        ->and($download->title)->toBe('Dispensa');
});
```

- [ ] **Step 2: Esegui i test e verifica che falliscano**

Run: `php artisan test --filter=Translatable`
Expected: FAIL (array to string conversion: i modelli non hanno il trait)

- [ ] **Step 3: Aggiungi il trait ai tre modelli**

In `app/Models/Course.php` aggiungi l'import e il trait:

```php
use Spatie\Translatable\HasTranslations;
```

```php
class Course extends Model
{
    use HasFactory;
    use HasTags;
    use HasTranslations;

    public array $translatable = ['title', 'description'];
```

In `app/Models/Video.php`, stessa cosa:

```php
use Spatie\Translatable\HasTranslations;
```

```php
class Video extends Model
{
    use HasTags;
    use HasTranslations;

    public array $translatable = ['title', 'description'];
```

In `app/Models/Download.php`, stessa cosa:

```php
use Spatie\Translatable\HasTranslations;
```

```php
class Download extends Model
{
    use HasFactory;
    use HasTags;
    use HasTranslations;

    public array $translatable = ['title', 'description'];
```

NON aggiungere cast `array` su title/description: ci pensa il trait.

- [ ] **Step 4: Crea la migration di conversione**

`database/migrations/2026_07_20_000002_convert_content_to_translatable.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** @var array<string, list<string>> */
    private array $tables = [
        'courses' => ['title', 'description'],
        'videos' => ['title', 'description'],
        'downloads' => ['title', 'description'],
    ];

    public function up(): void
    {
        // title è string(255): il JSON con più lingue può superarla
        foreach (array_keys($this->tables) as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->text('title')->change();
            });
        }

        foreach ($this->tables as $table => $columns) {
            DB::table($table)->orderBy('id')->chunkById(100, function ($rows) use ($table, $columns) {
                foreach ($rows as $row) {
                    $update = [];
                    foreach ($columns as $column) {
                        $value = $row->{$column};
                        if ($value !== null && json_decode($value, true) === null) {
                            $update[$column] = json_encode(['it' => $value], JSON_UNESCAPED_UNICODE);
                        }
                    }
                    if ($update !== []) {
                        DB::table($table)->where('id', $row->id)->update($update);
                    }
                }
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table => $columns) {
            DB::table($table)->orderBy('id')->chunkById(100, function ($rows) use ($table, $columns) {
                foreach ($rows as $row) {
                    $update = [];
                    foreach ($columns as $column) {
                        $decoded = json_decode($row->{$column} ?? '', true);
                        if (is_array($decoded)) {
                            $update[$column] = $decoded['it'] ?? reset($decoded);
                        }
                    }
                    if ($update !== []) {
                        DB::table($table)->where('id', $row->id)->update($update);
                    }
                }
            });
        }
    }
};
```

- [ ] **Step 5: Migra ed esegui i test**

Run: `php artisan migrate && php artisan test --filter=Translatable`
Expected: PASS (3 tests)

- [ ] **Step 6: Verifica i dati locali esistenti**

```bash
php artisan tinker --execute="echo App\Models\Course::first()?->title ?? 'nessun corso';"
```

Expected: il titolo del primo corso stampato correttamente (non JSON grezzo).

- [ ] **Step 7: Esegui l'intera suite**

Run: `php artisan test`
Expected: tutti PASS

- [ ] **Step 8: Commit**

```bash
git add app/Models/Course.php app/Models/Video.php app/Models/Download.php database/migrations/2026_07_20_000002_convert_content_to_translatable.php tests/Feature/TranslatableContentTest.php
git commit -m "Make course, video and download content translatable"
```

---

### Task 8: Search e filtri tag multilingua

**Files:**
- Modify: `app/Livewire/Course/Index.php:74-84`
- Modify: `app/Livewire/Video/Index.php:63-72`
- Test: `tests/Feature/TranslatableContentTest.php`

**Interfaces:**
- Consumes: modelli traducibili (Task 7).
- Produces: search su `title->{locale corrente}` + `title->it`; filtro tag che matcha il nome tag nella lingua corrente o in italiano.

- [ ] **Step 1: Scrivi i test che falliscono**

Aggiungi a `tests/Feature/TranslatableContentTest.php`:

```php
use App\Models\User;
use Livewire\Livewire;

test('la ricerca corsi trova sia la lingua corrente sia l italiano', function () {
    $user = User::factory()->create(['locale' => 'en']);
    Course::create(['title' => ['it' => 'Corso saldatura', 'en' => 'Welding course'], 'when' => now()->addDay()]);
    Course::create(['title' => ['it' => 'Corso bullonatura'], 'when' => now()->addDay()]);

    $this->actingAs($user);
    app()->setLocale('en');

    Livewire::test(\App\Livewire\Course\Index::class)
        ->set('search', 'Welding')
        ->assertSee('Welding course');

    Livewire::test(\App\Livewire\Course\Index::class)
        ->set('search', 'bullonatura')
        ->assertSee('Corso bullonatura');
});

test('la ricerca video trova sia la lingua corrente sia l italiano', function () {
    $user = User::factory()->create(['locale' => 'en']);
    Video::create(['title' => ['it' => 'Video montaggio', 'en' => 'Assembly video']]);

    $this->actingAs($user);
    app()->setLocale('en');

    Livewire::test(\App\Livewire\Video\Index::class)
        ->set('search', 'Assembly')
        ->assertSee('Assembly video');

    Livewire::test(\App\Livewire\Video\Index::class)
        ->set('search', 'montaggio')
        ->assertSee('Assembly video');
});
```

- [ ] **Step 2: Esegui i test e verifica che falliscano**

Run: `php artisan test --filter=ricerca`
Expected: FAIL (like su colonna JSON intera non matcha per lingua)

- [ ] **Step 3: Aggiorna la query in `app/Livewire/Course/Index.php`**

Nel metodo `render()`, sostituisci il blocco query con:

```php
$locale = app()->getLocale();

$courses = Course::query()
    ->when(!empty($this->selectedTags), function ($query) use ($locale) {
        $query->whereHas('tags', function ($q) use ($locale) {
            $q->where('type', 'categories')
                ->where(function ($q2) use ($locale) {
                    foreach ($this->selectedTags as $name) {
                        $q2->orWhere("name->{$locale}", $name)
                            ->orWhere('name->it', $name);
                    }
                });
        });
    })
    ->when($this->search, function ($query) use ($locale) {
        $query->where(function ($q) use ($locale) {
            $q->where("title->{$locale}", 'like', '%' . $this->search . '%')
                ->orWhere('title->it', 'like', '%' . $this->search . '%');
        });
    })
    ->with(['tags'])
    ->paginate(10);
```

- [ ] **Step 4: Aggiorna la query in `app/Livewire/Video/Index.php`**

Nel metodo `render()`, sostituisci il blocco query con:

```php
$locale = app()->getLocale();

$videos = Video::query()
    ->when(!empty($this->selectedTags), function ($query) use ($locale) {
        $query->whereHas('tags', function ($q) use ($locale) {
            $q->where('type', 'videos')
                ->where(function ($q2) use ($locale) {
                    foreach ($this->selectedTags as $name) {
                        $q2->orWhere("name->{$locale}", $name)
                            ->orWhere('name->it', $name);
                    }
                });
        });
    })
    ->when($this->search, function ($query) use ($locale) {
        $query->where(function ($q) use ($locale) {
            $q->where("title->{$locale}", 'like', '%' . $this->search . '%')
                ->orWhere('title->it', 'like', '%' . $this->search . '%');
        });
    })
    ->with(['tags'])
    ->paginate(10);
```

Nota: `Downloads/Index` filtra su Collection già caricate usando gli accessor localizzati — nessuna modifica necessaria.

- [ ] **Step 5: Esegui i test**

Run: `php artisan test --filter=ricerca`
Expected: PASS (2 tests)

- [ ] **Step 6: Commit**

```bash
git add app/Livewire/Course/Index.php app/Livewire/Video/Index.php tests/Feature/TranslatableContentTest.php
git commit -m "Make search and tag filters locale-aware"
```

---

### Task 9: File di lingua JSON (it/en/fr)

**Files:**
- Create: `lang/it.json`, `lang/en.json`, `lang/fr.json`

**Interfaces:**
- Produces: dizionari completi per tutte le chiavi usate da viste (Task 10) e notifiche (Task 12). Chiave = testo naturale.

- [ ] **Step 1: Crea `lang/en.json`**

Chiavi italiane → inglese (include chiavi esistenti auth/settings, nuove chiavi frontend e chiavi notifiche introdotte nei Task 10 e 12):

```json
{
    "Accedi": "Log in",
    "Accedi al tuo account": "Log in to your account",
    "Aggiorna il tema dell'applicazione per il tuo account.": "Update your account's appearance theme.",
    "Aggiorna il tuo profilo": "Update your profile",
    "Aggiorna la password del tuo account.": "Update your account password.",
    "Aggiorna password": "Update password",
    "Annulla": "Cancel",
    "Aspetto": "Appearance",
    "Cancella account": "Delete account",
    "Cancella il tuo account e tutte le sue risorse": "Delete your account and all of its resources",
    "Ciao, se il  tuo account esiste, riceverai una mail con un link per il reset della password!": "Hi, if your account exists, you will receive an email with a link to reset your password!",
    "Clicca qui per verificare la tua mail.": "Click here to verify your email.",
    "Conferma Password": "Confirm Password",
    "Conferma password": "Confirm password",
    "Corsi": "Courses",
    "Crea account": "Create account",
    "Crea un account": "Create an account",
    "Impostazione del tuo profilo": "Your profile settings",
    "Inserisci email e password per accedere": "Enter your email and password to log in",
    "Inserisci i dettagli richiesti qui sotto per creare un tuo account": "Enter the details below to create your account",
    "Inserisci la tua mail, per resettare la password": "Enter your email to reset your password",
    "La tua mail non è verificata.": "Your email is not verified.",
    "Nome": "Name",
    "Nome e cognome": "Full name",
    "Non hai un account?": "Don't have an account?",
    "Nuova password": "New password",
    "Oppure, torna al Login": "Or, go back to Login",
    "Password attuale": "Current password",
    "Password dimenticata": "Forgot password",
    "Password dimenticata?": "Forgot your password?",
    "Profilo": "Profile",
    "Registrati ora": "Sign up now",
    "Resetta password": "Reset password",
    "Ricorda accesso": "Remember me",
    "Salva": "Save",
    "Salvato.": "Saved.",
    "Sei già registrato?": "Already registered?",
    "Sei sicuro di voler cancellarlo?": "Are you sure you want to delete it?",
    "Torna al sito web": "Back to the website",
    "Un nuovo link di verifica è stato inviato al tuo account.": "A new verification link has been sent to your account.",
    "Una volta che hai cancellato non puoi tornare indietro. Inserisci la tua password per cancellare l'account definitivamente.": "Once deleted, there is no going back. Enter your password to permanently delete your account.",
    "Video": "Videos",
    "I Corsi di Metal.Ri": "Metal.Ri Courses",
    "Metal.Ri Academy nasce con l’obiettivo di promuovere la diffusione delle nuove tecnologie costruttive nell’ottica della prefabbricazione edilizia e dell’industrializzazione del cantiere, organizzando webinar, seminari e convegni finalizzati all’aggiornamento continuo delle conoscenze professionali, scientifiche e tecniche di coloro i quali operano nel campo delle costruzioni.": "Metal.Ri Academy was created to promote the spread of new construction technologies in the context of building prefabrication and site industrialisation, organising webinars, seminars and conferences aimed at continuously updating the professional, scientific and technical knowledge of those working in the construction field.",
    "Filtra per Categoria": "Filter by Category",
    "Cerca corsi...": "Search courses...",
    "Iscriviti": "Sign up",
    "Iscritto": "Enrolled",
    "Approfondisci": "Learn more",
    "Resetta filtri": "Reset filters",
    "Torna indietro": "Go back",
    "Risulti già iscritto a questo webinar": "You are already enrolled in this webinar",
    "Relatori": "Speakers",
    "Risorse Aggiuntive": "Additional Resources",
    "Nessuna biografia disponibile.": "No biography available.",
    "Raccolta di video": "Video collection",
    "I tuoi materiali disponibili al download": "Your materials available for download",
    "In questa sezione trovi tutti i materiali a te riservati: dispense, guide, risorse extra. Puoi filtrare per categoria per trovare più velocemente ciò che ti serve.": "In this section you will find all the materials reserved for you: handouts, guides, extra resources. You can filter by category to find what you need faster.",
    "Nessun download disponibile.": "No downloads available.",
    "Scarica": "Download",
    "Ciao e bentrovato": "Hello and welcome back",
    "Totale Download disponibili sulla piattaforma": "Total downloads available on the platform",
    "Vai i Download": "Go to Downloads",
    "Corsi pubblicati sulla piattaforma": "Courses published on the platform",
    "Vai ai Corsi": "Go to Courses",
    "Lingua": "Language",
    "Ciao, un nuovo corso è online: :title": "Hi, a new course is online: :title",
    "Dai un occhio": "Take a look",
    "Iscrizione Confermata - :title": "Registration Confirmed - :title",
    "Ciao :name!": "Hi :name!",
    "Ti sei iscritto al corso: :title": "You have enrolled in the course: :title",
    "Si terrà il giorno: :date": "It will take place on: :date",
    "Accedi al webinar": "Join the webinar",
    "Grazie per esserti iscritto!": "Thank you for enrolling!",
    "Nel caso in cui fosse richiesto": "In case it is required",
    "La password per accedere al webinar è: :password": "The password to join the webinar is: :password",
    "L'ID del webinar è: :id": "The webinar ID is: :id",
    "Cordiali saluti, Il Team": "Best regards, The Team",
    "Non specificata": "Not specified",
    "Non specificato": "Not specified",
    "Ciao, un nuovo download è disponibile: :title": "Hi, a new download is available: :title",
    "Dai un occhio ai tuoi download": "Check out your downloads",
    "Reimpostazione Password": "Password Reset",
    "Hai ricevuto questa email perché abbiamo ricevuto una richiesta di reimpostazione password per il tuo account.": "You are receiving this email because we received a password reset request for your account.",
    "Reimposta Password": "Reset Password",
    "Questo link di reimpostazione password scadrà tra 60 minuti.": "This password reset link will expire in 60 minutes.",
    "Se non hai richiesto tu la reimpostazione della password, non è necessaria alcuna azione.": "If you did not request a password reset, no further action is required.",
    "Ciao hai un nuovo utente registrato!": "Hi, you have a new registered user!",
    "Aggiungilo ai giusti gruppi!": "Add them to the right groups!",
    "Verifica il tuo Indirizzo Email": "Verify Your Email Address",
    "Clicca sul pulsante qui sotto per verificare il tuo indirizzo email.": "Click the button below to verify your email address.",
    "Verifica Email": "Verify Email",
    "Se non hai creato un account, non è necessaria alcuna azione.": "If you did not create an account, no further action is required."
}
```

ATTENZIONE: le due chiavi lunghe (intro corsi e intro download) e le chiavi con apostrofi DEVONO essere copiate carattere per carattere dalle viste originali (`resources/views/livewire/course/index.blade.php:7`, `resources/views/livewire/downloads/index.blade.php:9`), inclusi eventuali apostrofi tipografici `’` e doppi spazi. Nel Task 10 le viste vengono convertite usando il testo originale come chiave: chiave JSON e chiave nella vista devono coincidere esattamente.

- [ ] **Step 2: Crea `lang/fr.json`**

Tutte le chiavi di `en.json` più le chiavi inglesi del starter kit, tradotte in francese:

```json
{
    "Accedi": "Se connecter",
    "Accedi al tuo account": "Connectez-vous à votre compte",
    "Aggiorna il tema dell'applicazione per il tuo account.": "Mettez à jour le thème de l'application pour votre compte.",
    "Aggiorna il tuo profilo": "Mettez à jour votre profil",
    "Aggiorna la password del tuo account.": "Mettez à jour le mot de passe de votre compte.",
    "Aggiorna password": "Mettre à jour le mot de passe",
    "Annulla": "Annuler",
    "Aspetto": "Apparence",
    "Cancella account": "Supprimer le compte",
    "Cancella il tuo account e tutte le sue risorse": "Supprimez votre compte et toutes ses ressources",
    "Ciao, se il  tuo account esiste, riceverai una mail con un link per il reset della password!": "Bonjour, si votre compte existe, vous recevrez un e-mail avec un lien pour réinitialiser votre mot de passe !",
    "Clicca qui per verificare la tua mail.": "Cliquez ici pour vérifier votre e-mail.",
    "Conferma Password": "Confirmer le mot de passe",
    "Conferma password": "Confirmer le mot de passe",
    "Corsi": "Formations",
    "Crea account": "Créer un compte",
    "Crea un account": "Créer un compte",
    "Impostazione del tuo profilo": "Paramètres de votre profil",
    "Inserisci email e password per accedere": "Saisissez votre e-mail et votre mot de passe pour vous connecter",
    "Inserisci i dettagli richiesti qui sotto per creare un tuo account": "Saisissez les informations ci-dessous pour créer votre compte",
    "Inserisci la tua mail, per resettare la password": "Saisissez votre e-mail pour réinitialiser votre mot de passe",
    "La tua mail non è verificata.": "Votre e-mail n'est pas vérifié.",
    "Nome": "Nom",
    "Nome e cognome": "Nom et prénom",
    "Non hai un account?": "Vous n'avez pas de compte ?",
    "Nuova password": "Nouveau mot de passe",
    "Oppure, torna al Login": "Ou revenez à la connexion",
    "Password attuale": "Mot de passe actuel",
    "Password dimenticata": "Mot de passe oublié",
    "Password dimenticata?": "Mot de passe oublié ?",
    "Profilo": "Profil",
    "Registrati ora": "Inscrivez-vous maintenant",
    "Resetta password": "Réinitialiser le mot de passe",
    "Ricorda accesso": "Se souvenir de moi",
    "Salva": "Enregistrer",
    "Salvato.": "Enregistré.",
    "Sei già registrato?": "Déjà inscrit ?",
    "Sei sicuro di voler cancellarlo?": "Êtes-vous sûr de vouloir le supprimer ?",
    "Torna al sito web": "Retour au site web",
    "Un nuovo link di verifica è stato inviato al tuo account.": "Un nouveau lien de vérification a été envoyé à votre compte.",
    "Una volta che hai cancellato non puoi tornare indietro. Inserisci la tua password per cancellare l'account definitivamente.": "Une fois supprimé, il n'y a pas de retour en arrière. Saisissez votre mot de passe pour supprimer définitivement votre compte.",
    "Video": "Vidéos",
    "I Corsi di Metal.Ri": "Les formations Metal.Ri",
    "Metal.Ri Academy nasce con l’obiettivo di promuovere la diffusione delle nuove tecnologie costruttive nell’ottica della prefabbricazione edilizia e dell’industrializzazione del cantiere, organizzando webinar, seminari e convegni finalizzati all’aggiornamento continuo delle conoscenze professionali, scientifiche e tecniche di coloro i quali operano nel campo delle costruzioni.": "Metal.Ri Academy est née dans le but de promouvoir la diffusion des nouvelles technologies de construction dans une optique de préfabrication du bâtiment et d'industrialisation du chantier, en organisant des webinaires, des séminaires et des conférences visant à l'actualisation continue des connaissances professionnelles, scientifiques et techniques de ceux qui travaillent dans le domaine de la construction.",
    "Filtra per Categoria": "Filtrer par catégorie",
    "Cerca corsi...": "Rechercher des formations...",
    "Iscriviti": "S'inscrire",
    "Iscritto": "Inscrit",
    "Approfondisci": "En savoir plus",
    "Resetta filtri": "Réinitialiser les filtres",
    "Torna indietro": "Retour",
    "Risulti già iscritto a questo webinar": "Vous êtes déjà inscrit à ce webinaire",
    "Relatori": "Intervenants",
    "Risorse Aggiuntive": "Ressources supplémentaires",
    "Nessuna biografia disponibile.": "Aucune biographie disponible.",
    "Raccolta di video": "Collection de vidéos",
    "I tuoi materiali disponibili al download": "Vos documents disponibles au téléchargement",
    "In questa sezione trovi tutti i materiali a te riservati: dispense, guide, risorse extra. Puoi filtrare per categoria per trovare più velocemente ciò che ti serve.": "Dans cette section, vous trouverez tous les documents qui vous sont réservés : supports, guides, ressources supplémentaires. Vous pouvez filtrer par catégorie pour trouver plus rapidement ce dont vous avez besoin.",
    "Nessun download disponibile.": "Aucun téléchargement disponible.",
    "Scarica": "Télécharger",
    "Ciao e bentrovato": "Bonjour et bienvenue",
    "Totale Download disponibili sulla piattaforma": "Total des téléchargements disponibles sur la plateforme",
    "Vai i Download": "Voir les téléchargements",
    "Corsi pubblicati sulla piattaforma": "Formations publiées sur la plateforme",
    "Vai ai Corsi": "Voir les formations",
    "Lingua": "Langue",
    "Ciao, un nuovo corso è online: :title": "Bonjour, une nouvelle formation est en ligne : :title",
    "Dai un occhio": "Jetez un œil",
    "Iscrizione Confermata - :title": "Inscription confirmée - :title",
    "Ciao :name!": "Bonjour :name !",
    "Ti sei iscritto al corso: :title": "Vous vous êtes inscrit à la formation : :title",
    "Si terrà il giorno: :date": "Elle aura lieu le : :date",
    "Accedi al webinar": "Accéder au webinaire",
    "Grazie per esserti iscritto!": "Merci de votre inscription !",
    "Nel caso in cui fosse richiesto": "Au cas où cela serait demandé",
    "La password per accedere al webinar è: :password": "Le mot de passe pour accéder au webinaire est : :password",
    "L'ID del webinar è: :id": "L'identifiant du webinaire est : :id",
    "Cordiali saluti, Il Team": "Cordialement, L'équipe",
    "Non specificata": "Non spécifié",
    "Non specificato": "Non spécifié",
    "Ciao, un nuovo download è disponibile: :title": "Bonjour, un nouveau téléchargement est disponible : :title",
    "Dai un occhio ai tuoi download": "Consultez vos téléchargements",
    "Reimpostazione Password": "Réinitialisation du mot de passe",
    "Hai ricevuto questa email perché abbiamo ricevuto una richiesta di reimpostazione password per il tuo account.": "Vous recevez cet e-mail car nous avons reçu une demande de réinitialisation du mot de passe de votre compte.",
    "Reimposta Password": "Réinitialiser le mot de passe",
    "Questo link di reimpostazione password scadrà tra 60 minuti.": "Ce lien de réinitialisation expirera dans 60 minutes.",
    "Se non hai richiesto tu la reimpostazione della password, non è necessaria alcuna azione.": "Si vous n'avez pas demandé de réinitialisation, aucune action n'est requise.",
    "Ciao hai un nuovo utente registrato!": "Bonjour, vous avez un nouvel utilisateur inscrit !",
    "Aggiungilo ai giusti gruppi!": "Ajoutez-le aux bons groupes !",
    "Verifica il tuo Indirizzo Email": "Vérifiez votre adresse e-mail",
    "Clicca sul pulsante qui sotto per verificare il tuo indirizzo email.": "Cliquez sur le bouton ci-dessous pour vérifier votre adresse e-mail.",
    "Verifica Email": "Vérifier l'e-mail",
    "Se non hai creato un account, non è necessaria alcuna azione.": "Si vous n'avez pas créé de compte, aucune action n'est requise.",
    "Platform": "Plateforme",
    "Settings": "Paramètres",
    "Log Out": "Se déconnecter",
    "Log out": "Se déconnecter",
    "Log in": "Se connecter",
    "log in": "se connecter",
    "Search": "Rechercher",
    "Saved.": "Enregistré.",
    "Confirm": "Confirmer",
    "Confirm password": "Confirmer le mot de passe",
    "Light": "Clair",
    "Dark": "Sombre",
    "System": "Système",
    "Dashboard": "Tableau de bord",
    "Downloads": "Téléchargements",
    "Email": "E-mail",
    "Password": "Mot de passe",
    "Documentation": "Documentation",
    "Repository": "Dépôt",
    "All rights reserved.": "Tous droits réservés.",
    "Reset password": "Réinitialiser le mot de passe",
    "Resend verification email": "Renvoyer l'e-mail de vérification",
    "A new verification link has been sent to the email address you provided during registration.": "Un nouveau lien de vérification a été envoyé à l'adresse e-mail fournie lors de l'inscription.",
    "Please verify your email address by clicking on the link we just emailed to you.": "Veuillez vérifier votre adresse e-mail en cliquant sur le lien que nous venons de vous envoyer.",
    "Please enter your new password below": "Veuillez saisir votre nouveau mot de passe ci-dessous",
    "This is a secure area of the application. Please confirm your password before continuing.": "Ceci est une zone sécurisée de l'application. Veuillez confirmer votre mot de passe avant de continuer.",
    "No Image": "Aucune image"
}
```

- [ ] **Step 3: Crea `lang/it.json`**

Chiavi inglesi del starter kit → italiano:

```json
{
    "Platform": "Piattaforma",
    "Settings": "Impostazioni",
    "Log Out": "Esci",
    "Log out": "Esci",
    "Log in": "Accedi",
    "log in": "accedi",
    "Search": "Cerca",
    "Saved.": "Salvato.",
    "Confirm": "Conferma",
    "Confirm password": "Conferma password",
    "Light": "Chiaro",
    "Dark": "Scuro",
    "System": "Sistema",
    "Downloads": "Download",
    "Documentation": "Documentazione",
    "All rights reserved.": "Tutti i diritti riservati.",
    "Reset password": "Reimposta password",
    "Resend verification email": "Invia di nuovo l'email di verifica",
    "A new verification link has been sent to the email address you provided during registration.": "Un nuovo link di verifica è stato inviato all'indirizzo email fornito durante la registrazione.",
    "Please verify your email address by clicking on the link we just emailed to you.": "Verifica il tuo indirizzo email cliccando sul link che ti abbiamo appena inviato.",
    "Please enter your new password below": "Inserisci qui sotto la tua nuova password",
    "This is a secure area of the application. Please confirm your password before continuing.": "Questa è un'area protetta dell'applicazione. Conferma la tua password prima di continuare.",
    "No Image": "Nessuna immagine"
}
```

- [ ] **Step 4: Valida i JSON**

```bash
php -r "foreach (['it','en','fr'] as \$l) { json_decode(file_get_contents('lang/'.\$l.'.json'), true, 512, JSON_THROW_ON_ERROR); echo \$l.' ok'.PHP_EOL; }"
```

Expected: `it ok`, `en ok`, `fr ok`

- [ ] **Step 5: Commit**

```bash
git add lang/it.json lang/en.json lang/fr.json
git commit -m "Add IT/EN/FR JSON translation files"
```

---

### Task 10: Conversione viste a `__()` e date localizzate

**Files:**
- Modify: `resources/views/livewire/course/index.blade.php`
- Modify: `resources/views/courses/show.blade.php`
- Modify: `resources/views/livewire/video/index.blade.php`
- Modify: `resources/views/livewire/downloads/index.blade.php`
- Modify: `resources/views/dashboard.blade.php`
- Test: `tests/Feature/LocalizationTest.php`

**Interfaces:**
- Consumes: file JSON (Task 9), middleware SetLocale (Task 3).
- Produces: frontend interamente traducibile. Il testo originale italiano è la chiave: la vista convertita e la chiave JSON devono coincidere carattere per carattere.

- [ ] **Step 1: Scrivi i test che falliscono**

Aggiungi a `tests/Feature/LocalizationTest.php`:

```php
test('la pagina corsi è tradotta in inglese per un utente inglese', function () {
    $user = User::factory()->create(['locale' => 'en']);

    $this->actingAs($user)->get('/courses')
        ->assertSee('Filter by Category')
        ->assertSee('Metal.Ri Courses');
});

test('la pagina download è tradotta in francese per un utente francese', function () {
    $user = User::factory()->create(['locale' => 'fr']);

    $this->actingAs($user)->get('/downloads')
        ->assertSee('Filtrer par catégorie');
});
```

- [ ] **Step 2: Esegui i test e verifica che falliscano**

Run: `php artisan test --filter="tradotta in"`
Expected: FAIL (testo hardcoded italiano)

- [ ] **Step 3: Converti `resources/views/livewire/course/index.blade.php`**

Sostituzioni esatte (testo attuale → nuovo codice):

| Attuale | Nuovo |
|---|---|
| `I Corsi di Metal.Ri` | `{{ __('I Corsi di Metal.Ri') }}` |
| Paragrafo intro Academy (riga ~7) | `{{ __('Metal.Ri Academy nasce con l’obiettivo di promuovere la diffusione delle nuove tecnologie costruttive nell’ottica della prefabbricazione edilizia e dell’industrializzazione del cantiere, organizzando webinar, seminari e convegni finalizzati all’aggiornamento continuo delle conoscenze professionali, scientifiche e tecniche di coloro i quali operano nel campo delle costruzioni.') }}` (apostrofi tipografici `’`: identici alla vista originale e alle chiavi JSON) |
| `Filtra per Categoria` | `{{ __('Filtra per Categoria') }}` |
| `placeholder="Cerca corsi..."` | `:placeholder="__('Cerca corsi...')"` |
| `Iscriviti` (bottone card) | `{{ __('Iscriviti') }}` |
| `Iscritto` (bottone disabled) | `{{ __('Iscritto') }}` |
| `Approfondisci` | `{{ __('Approfondisci') }}` |
| `Resetta filtri` | `{{ __('Resetta filtri') }}` |
| `{{ $course->when->locale('it')->isoFormat('ddd MMM YYYY') }}` | `{{ $course->when->locale(app()->getLocale())->isoFormat('ddd MMM YYYY') }}` |

Per il paragrafo intro: seleziona il testo esatto dalla vista (con i suoi apostrofi tipografici), incollalo come chiave dentro `__('...')` facendo escape degli apostrofi dritti se presenti, e verifica che la chiave in `lang/en.json`/`lang/fr.json` sia identica (aggiorna il JSON se l'originale usa `’` dove il piano ha `'`).

- [ ] **Step 4: Converti `resources/views/courses/show.blade.php`**

| Attuale | Nuovo |
|---|---|
| `Torna indietro` | `{{ __('Torna indietro') }}` |
| `Iscriviti` | `{{ __('Iscriviti') }}` |
| `Risulti già iscritto a questo webinar` | `{{ __('Risulti già iscritto a questo webinar') }}` |
| `Relatori` | `{{ __('Relatori') }}` |
| `Risorse Aggiuntive` | `{{ __('Risorse Aggiuntive') }}` |
| `Nessuna biografia disponibile.` | `{{ __('Nessuna biografia disponibile.') }}` |
| `No Image` | `{{ __('No Image') }}` |
| `{{ $course->when->format("D M Y H:i")}}` | `{{ $course->when->locale(app()->getLocale())->isoFormat('ddd D MMM YYYY HH:mm') }}` |

- [ ] **Step 5: Converti `resources/views/livewire/video/index.blade.php`**

| Attuale | Nuovo |
|---|---|
| `Raccolta di video` | `{{ __('Raccolta di video') }}` |
| `Filtra per Categoria` | `{{ __('Filtra per Categoria') }}` |
| `Resetta filtri` | `{{ __('Resetta filtri') }}` |

- [ ] **Step 6: Converti `resources/views/livewire/downloads/index.blade.php`**

| Attuale | Nuovo |
|---|---|
| `I tuoi materiali disponibili al download` | `{{ __('I tuoi materiali disponibili al download') }}` |
| Paragrafo intro download (riga ~9) | `{{ __('In questa sezione trovi tutti i materiali a te riservati: dispense, guide, risorse extra. Puoi filtrare per categoria per trovare più velocemente ciò che ti serve.') }}` |
| `Filtra per Categoria` | `{{ __('Filtra per Categoria') }}` |
| `Resetta filtri` | `{{ __('Resetta filtri') }}` |
| `Nessun download disponibile.` | `{{ __('Nessun download disponibile.') }}` |
| `Scarica` | `{{ __('Scarica') }}` |

- [ ] **Step 7: Converti `resources/views/dashboard.blade.php`**

| Attuale | Nuovo |
|---|---|
| `Ciao e bentrovato` | `{{ __('Ciao e bentrovato') }}` |
| `Totale Download disponibili sulla piattaforma` | `{{ __('Totale Download disponibili sulla piattaforma') }}` |
| `Vai i Download` | `{{ __('Vai i Download') }}` |
| `Corsi pubblicati sulla piattaforma` | `{{ __('Corsi pubblicati sulla piattaforma') }}` |
| `Vai ai Corsi` | `{{ __('Vai ai Corsi') }}` |

Controlla anche eventuali altre stringhe visibili nella card video della dashboard e trattale allo stesso modo (testo → chiave in vista + entry in en.json/fr.json).

- [ ] **Step 8: Esegui i test**

Run: `php artisan test --filter="tradotta in"`
Expected: PASS (2 tests)

- [ ] **Step 9: Verifica manuale nelle tre lingue**

Run: `php artisan serve`, login, cambia lingua con lo switcher e visita `/dashboard`, `/courses`, un dettaglio corso, `/video`, `/downloads` in EN e FR. Nessun testo italiano residuo (eccetto contenuti DB non ancora tradotti, che è il fallback atteso).

- [ ] **Step 10: Commit**

```bash
git add resources/views tests/Feature/LocalizationTest.php lang/en.json lang/fr.json
git commit -m "Translate frontend views with __() and localized dates"
```

---

### Task 11: File PHP di lingua francese (validazioni, auth)

**Files:**
- Create: `lang/fr/auth.php`, `lang/fr/pagination.php`, `lang/fr/passwords.php`, `lang/fr/validation.php`
- Test: `tests/Feature/LocalizationTest.php`

**Interfaces:**
- Produces: messaggi di sistema Laravel in francese, stessa struttura di `lang/it/` e `lang/en/`.

- [ ] **Step 1: Scrivi il test che fallisce**

Aggiungi a `tests/Feature/LocalizationTest.php`:

```php
test('i messaggi di validazione sono tradotti in francese', function () {
    app()->setLocale('fr');

    expect(__('validation.required', ['attribute' => 'email']))
        ->not->toBe('validation.required')
        ->toContain('obligatoire');
});
```

- [ ] **Step 2: Esegui il test e verifica che fallisca**

Run: `php artisan test --filter=francese`
Expected: FAIL (chiave non tradotta)

- [ ] **Step 3: Installa Laravel-Lang e aggiungi il francese**

```bash
composer require laravel-lang/common --dev
php artisan lang:add fr
```

Expected: creati i file in `lang/fr/`. Se il comando pubblica anche `fr.json`, uniscilo a mano con il nostro `lang/fr.json` (le nostre chiavi vincono) oppure scarta le righe non pertinenti.

- [ ] **Step 4: Esegui il test**

Run: `php artisan test --filter=francese`
Expected: PASS

- [ ] **Step 5: Commit**

```bash
git add lang/fr composer.json composer.lock lang/fr.json
git commit -m "Add French system translations"
```

---

### Task 12: Notifiche localizzate

**Files:**
- Modify: `app/Notifications/CourseCreatedNotification.php:38-42`
- Modify: `app/Notifications/CourseJoinedNotification.php:37-52`
- Modify: `app/Notifications/DownloadAviableNotification.php:36-42`
- Modify: `app/Notifications/ResetPasswordNotification.php:42-56`
- Modify: `app/Notifications/UserCreatedNotification.php:34-42`
- Modify: `app/Notifications/VerifyEmailNotification.php:17-26`
- Test: `tests/Feature/LocalizationTest.php`

**Interfaces:**
- Consumes: chiavi JSON (Task 9), `preferredLocale()` (Task 2 — il mail channel la usa automaticamente all'invio).
- Produces: tutte le `toMail` costruite con `__()`.

- [ ] **Step 1: Scrivi i test che falliscono**

Aggiungi a `tests/Feature/LocalizationTest.php`:

```php
use App\Models\Course;
use App\Notifications\CourseCreatedNotification;
use App\Notifications\CourseJoinedNotification;

test('la mail nuovo corso è tradotta secondo il locale attivo', function () {
    $course = Course::create(['title' => ['it' => 'Corso saldatura', 'fr' => 'Formation soudage'], 'when' => now()->addDay()]);
    $user = User::factory()->create(['locale' => 'fr']);

    app()->setLocale('fr');
    $mail = (new CourseCreatedNotification($course))->toMail($user);

    expect($mail->introLines[0])->toContain('Formation soudage')
        ->and($mail->introLines[0])->toContain('Bonjour');
});

test('la mail iscrizione corso è tradotta secondo il locale attivo', function () {
    $course = Course::create(['title' => ['it' => 'Corso saldatura', 'en' => 'Welding course'], 'when' => now()->addDay(), 'webinar_url' => 'https://example.com']);
    $user = User::factory()->create(['locale' => 'en']);

    app()->setLocale('en');
    $mail = (new CourseJoinedNotification($course, $user))->toMail($user);

    expect($mail->subject)->toBe('Registration Confirmed - Welding course');
});
```

Nota: il test Task 7 richiede `Course` già importato nel file — evita import duplicati.

- [ ] **Step 2: Esegui i test e verifica che falliscano**

Run: `php artisan test --filter="mail"`
Expected: FAIL (testi hardcoded italiani)

- [ ] **Step 3: Converti le 6 notifiche**

`CourseCreatedNotification::toMail`:

```php
public function toMail(object $notifiable): MailMessage
{
    return (new MailMessage)
        ->line(__('Ciao, un nuovo corso è online: :title', ['title' => $this->course->title]))
        ->action(__('Dai un occhio'), route('courses.show', $this->course->id));
}
```

`CourseJoinedNotification::toMail`:

```php
public function toMail(object $notifiable): MailMessage
{
    return (new MailMessage)
        ->subject(__('Iscrizione Confermata - :title', ['title' => $this->course->title]))
        ->greeting(__('Ciao :name!', ['name' => $this->user->name]))
        ->line(__('Ti sei iscritto al corso: :title', ['title' => $this->course->title]))
        ->line(__('Si terrà il giorno: :date', ['date' => $this->course->when->format('d/m/Y H:i')]))
        ->action(__('Accedi al webinar'), url($this->course->webinar_url))
        ->line(__('Grazie per esserti iscritto!'))
        ->line(__('Nel caso in cui fosse richiesto'))
        ->line(__('La password per accedere al webinar è: :password', ['password' => $this->course->webinar_password ?? __('Non specificata')]))
        ->line(__('L\'ID del webinar è: :id', ['id' => $this->course->webinar_id ?? __('Non specificato')]))
        ->salutation(__('Cordiali saluti, Il Team'));
}
```

`DownloadAviableNotification::toMail`:

```php
public function toMail(object $notifiable): MailMessage
{
    return (new MailMessage)
        ->line(__('Ciao, un nuovo download è disponibile: :title', ['title' => $this->download->title]))
        ->action(__('Dai un occhio ai tuoi download'), route('downloads.index'));
}
```

`ResetPasswordNotification::toMail` (solo il return, `$url` invariato):

```php
return (new MailMessage)
    ->subject(__('Reimpostazione Password'))
    ->line(__('Hai ricevuto questa email perché abbiamo ricevuto una richiesta di reimpostazione password per il tuo account.'))
    ->action(__('Reimposta Password'), $url)
    ->line(__('Questo link di reimpostazione password scadrà tra 60 minuti.'))
    ->line(__('Se non hai richiesto tu la reimpostazione della password, non è necessaria alcuna azione.'));
```

`UserCreatedNotification::toMail`:

```php
public function toMail(object $notifiable): MailMessage
{
    return (new MailMessage)
        ->line(__('Ciao hai un nuovo utente registrato!'))
        ->action(__('Aggiungilo ai giusti gruppi!'), url('/'))
        ->line('Metal.Ri Academy');
}
```

`VerifyEmailNotification::toMail`:

```php
public function toMail($notifiable): MailMessage
{
    $verificationUrl = $this->verificationUrl($notifiable);

    return (new MailMessage)
        ->subject(__('Verifica il tuo Indirizzo Email'))
        ->line(__('Clicca sul pulsante qui sotto per verificare il tuo indirizzo email.'))
        ->action(__('Verifica Email'), $verificationUrl)
        ->line(__('Se non hai creato un account, non è necessaria alcuna azione.'));
}
```

- [ ] **Step 4: Esegui i test**

Run: `php artisan test --filter="mail"`
Expected: PASS (2 tests)

- [ ] **Step 5: Esegui l'intera suite**

Run: `php artisan test`
Expected: tutti PASS

- [ ] **Step 6: Commit**

```bash
git add app/Notifications tests/Feature/LocalizationTest.php
git commit -m "Localize notification emails"
```

---

### Task 13: Filament — editing contenuti per lingua

**Files:**
- Modify: `app/Providers/Filament/AdminPanelProvider.php`
- Modify: `app/Filament/Resources/CourseResource.php` + `CourseResource/Pages/{ListCourses,CreateCourse,EditCourse}.php`
- Modify: `app/Filament/Resources/VideoResource.php` + `VideoResource/Pages/{ListVideos,CreateVideo,EditVideo}.php`
- Modify: `app/Filament/Resources/DownloadResource.php` + `DownloadResource/Pages/{ListDownloads,CreateDownload,EditDownload}.php`
- Modify: `app/Filament/Resources/TagResource.php` + `TagResource/Pages/{ListTags,CreateTag,EditTag}.php`
- Test: `tests/Feature/FilamentTranslatableTest.php` (nuovo)

**Interfaces:**
- Consumes: modelli traducibili (Task 7), plugin (Task 1).
- Produces: selettore lingua (LocaleSwitcher) su tutte le pagine dei 4 resource; salvataggio per-locale automatico.

- [ ] **Step 1: Scrivi il test che fallisce**

Crea `tests/Feature/FilamentTranslatableTest.php`:

```php
<?php

use App\Filament\Resources\CourseResource;
use App\Models\User;
use Spatie\Permission\Models\Role;

test('l admin apre la pagina di creazione corso', function () {
    Role::create(['name' => 'super_admin']);
    $admin = User::factory()->create();
    $admin->assignRole('super_admin');

    $this->actingAs($admin)
        ->get(CourseResource::getUrl('create'))
        ->assertSuccessful();
});
```

- [ ] **Step 2: Esegui il test (baseline)**

Run: `php artisan test --filter=FilamentTranslatable`
Expected: PASS già ora (è il guard-rail di non-regressione per le modifiche che seguono). Se FAIL, fermati e sistema il setup ruoli prima di procedere.

- [ ] **Step 3: Registra il plugin sul panel**

In `app/Providers/Filament/AdminPanelProvider.php` sostituisci il blocco `->plugins([...])` (nota: oggi FilamentShieldPlugin è registrato due volte — la duplicazione va rimossa):

```php
->plugins([
    FilamentShieldPlugin::make(),
    \Filament\SpatieLaravelTranslatablePlugin::make()
        ->defaultLocales(['it', 'en', 'fr']),
])
```

- [ ] **Step 4: Rendi traducibile CourseResource**

In `app/Filament/Resources/CourseResource.php`:

```php
use Filament\Resources\Concerns\Translatable;

class CourseResource extends Resource
{
    use Translatable;
```

In `app/Filament/Resources/CourseResource/Pages/ListCourses.php`:

```php
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCourses extends ListRecords
{
    use ListRecords\Concerns\Translatable;
```

e nel metodo `getHeaderActions()` aggiungi `Actions\LocaleSwitcher::make(),` come primo elemento dell'array.

In `CreateCourse.php`:

```php
class CreateCourse extends CreateRecord
{
    use CreateRecord\Concerns\Translatable;
```

con `Actions\LocaleSwitcher::make()` in `getHeaderActions()` (aggiungi il metodo se non esiste):

```php
protected function getHeaderActions(): array
{
    return [
        Actions\LocaleSwitcher::make(),
    ];
}
```

In `EditCourse.php`:

```php
class EditCourse extends EditRecord
{
    use EditRecord\Concerns\Translatable;
```

con `Actions\LocaleSwitcher::make(),` aggiunto in testa a `getHeaderActions()`.

- [ ] **Step 5: Rendi traducibili VideoResource e DownloadResource**

Applica esattamente le stesse modifiche del passo 4 a:
- `VideoResource.php` (`use Translatable;`) e le sue pagine `ListVideos`, `CreateVideo`, `EditVideo` (trait `ListRecords\Concerns\Translatable` / `CreateRecord\Concerns\Translatable` / `EditRecord\Concerns\Translatable` + `Actions\LocaleSwitcher::make()` nei rispettivi `getHeaderActions()`).
- `DownloadResource.php` e le sue pagine `ListDownloads`, `CreateDownload`, `EditDownload` (identico).

- [ ] **Step 6: Rendi traducibile TagResource**

In `app/Filament/Resources/TagResource.php` aggiungi `use Filament\Resources\Concerns\Translatable;` + `use Translatable;` nella classe, e dai al form un campo nome (oggi lo schema è vuoto):

```php
public static function form(Form $form): Form
{
    return $form
        ->schema([
            Forms\Components\TextInput::make('name')
                ->label('Nome')
                ->required(),
        ]);
}
```

Pagine `ListTags`, `CreateTag`, `EditTag`: stessi trait e `LocaleSwitcher` del passo 4.

- [ ] **Step 7: Esegui il test e la suite**

Run: `php artisan test --filter=FilamentTranslatable && php artisan test`
Expected: tutti PASS

- [ ] **Step 8: Verifica manuale in admin**

Run: `php artisan serve`, login come super-admin su `/admin`: su Corsi/Video/Download/Tag le pagine di creazione e modifica mostrano il selettore lingua in alto a destra; compilando titolo in IT e EN e salvando, i valori restano distinti per lingua.

- [ ] **Step 9: Commit**

```bash
git add app/Providers/Filament/AdminPanelProvider.php app/Filament/Resources tests/Feature/FilamentTranslatableTest.php
git commit -m "Enable per-locale content editing in Filament"
```

---

## Note per il deploy

1. `composer install`
2. Aggiungere `APP_FALLBACK_LOCALE=it` al `.env` di produzione (verificare `APP_LOCALE=it`)
3. `php artisan migrate` (converte i contenuti esistenti sotto la chiave `it` — fare backup DB prima)
4. `php artisan config:clear && php artisan view:clear`
