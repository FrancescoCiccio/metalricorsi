# Design: Piattaforma multilingua (IT principale, EN, FR)

**Data:** 2026-07-20
**Stato:** Approvato

## Obiettivo

Rendere la piattaforma Metal.Ri Academy multilingua: italiano (lingua principale e di fallback), inglese e francese. Sono coinvolti sia l'interfaccia (viste, email) sia i contenuti nel database (corsi, video, download, categorie), gestibili da Filament.

## Decisioni chiave

- **Ambito:** interfaccia + contenuti DB.
- **Selezione lingua:** preferenza utente + switcher; nessun prefisso URL.
- **Fallback contenuti:** se una traduzione manca, si mostra la versione italiana (nessun contenuto viene nascosto).
- **Approccio contenuti:** colonne JSON con `spatie/laravel-translatable` + plugin ufficiale `filament/spatie-laravel-translatable-plugin` (Opzione A). Scartate le tabelle di traduzione separate (nessun plugin ufficiale Filament) e le colonne duplicate per lingua (rigide, non scalabili).
- **Campi array (`relators`, `additional_resources`):** NON tradotti in v1; bio relatori e nomi risorse restano in italiano per tutte le lingue. Eventuale traduzione in fase 2.
- **Ricerca:** cerca nel titolo della lingua corrente E in quello italiano (coerente con il fallback).
- **Admin Filament:** resta in italiano come interfaccia; i contenuti si compilano lingua per lingua tramite il selettore del plugin.

## Sezione 1 — Campi DB traducibili

### Course (`courses`)

| Campo | Traducibile | Note |
|---|---|---|
| `title` | Sì | Usato anche dalla search |
| `description` | Sì | HTML, dettaglio corso |
| `location` | No | Indirizzo/luogo |
| `relators` | No (v1) | Array {name, photo, bio}; bio resta in italiano |
| `additional_resources` | No (v1) | Array {name, file_path}; name resta in italiano |
| `when`, `online`, `max_attends`, `cover_path`, `youtube_embed`, `miniature_url`, `webinar_url`, `webinar_password`, `webinar_id`, `order` | No | Dati non testuali |

### Video (`videos`)

`title` e `description` traducibili. Search sul title da adattare.

### Download (`downloads`)

`title` e `description` traducibili.

### Group (`groups`)

Nessun campo traducibile: raggruppamento interno admin, mai mostrato all'utente.

### Tag / Categorie (`tags`)

Già traducibili nativamente (`spatie/laravel-tags` salva `name` e `slug` come JSON). Va solo abilitata la traduzione nel `TagResource` Filament. Le categorie compaiono nei filtri "Filtra per Categoria" di Corsi, Video e Download.

## Sezione 2 — Interfaccia

### Stringhe hardcoded da portare sotto `__()`

- `resources/views/livewire/course/index.blade.php`: "I Corsi di Metal.Ri", paragrafo introduttivo Academy, "Filtra per Categoria", "Cerca corsi...", stringhe delle card.
- `resources/views/courses/show.blade.php`: "Torna indietro", "Iscriviti", "Risulti già iscritto a questo webinar", "Relatori", "Risorse Aggiuntive", "Nessuna biografia disponibile", "No Image".
- `resources/views/livewire/video/index.blade.php`: "Raccolta di video", "Filtra per Categoria".
- `resources/views/livewire/downloads/index.blade.php`: "I tuoi materiali disponibili al download", paragrafo introduttivo, "Filtra per Categoria", "Nessun download disponibile.", "Scarica".
- `resources/views/dashboard.blade.php`: "Ciao e bentrovato" e altre stringhe delle card.
- `resources/views/components/layouts/app/sidebar.blade.php`: chiavi già sotto `__()` (miste italiano/inglese).

### File di lingua

- `lang/it.json`, `lang/en.json`, `lang/fr.json` completi: coprono sia le nuove chiavi italiane sia le chiavi inglesi del starter kit (auth, settings) — che oggi non hanno traduzione italiana e quindi appaiono in inglese.
- `lang/fr/` (auth.php, validation.php, passwords.php, pagination.php) dal pacchetto ufficiale Laravel-Lang.
- Convenzione chiavi: testo naturale come chiave (`__('Iscriviti')`), coerente con l'esistente. Ogni file JSON copre tutte le chiavi non native della propria lingua.

### Email / Notifiche

Le 6 notifiche (`CourseCreatedNotification`, `CourseJoinedNotification`, `DownloadAviableNotification`, `ResetPasswordNotification`, `UserCreatedNotification`, `VerifyEmailNotification`) hanno testi italiani hardcoded: passano a `__()`. L'invio avviene nella lingua del destinatario (vedi Sezione 3).

## Sezione 3 — Selezione lingua e switcher

- **Migration:** colonna `locale` (string, nullable, default `it`) su `users`.
- **Middleware `SetLocale`** nel gruppo `web`. Priorità: preferenza utente loggato → sessione (ospiti) → header `Accept-Language` → fallback `it`. Locali supportati: `it`, `en`, `fr`.
- **Switcher lingua** (dropdown IT/EN/FR): nella sidebar dell'app e nell'header delle pagine auth per gli ospiti. Al cambio salva su `users.locale` (se loggato) e in sessione, poi ricarica la pagina.
- **Impostazioni profilo:** campo "Lingua" nella pagina `settings/profile`.
- **`.env` / config:** `APP_LOCALE=it`, `APP_FALLBACK_LOCALE=it`.
- **Email nella lingua giusta:** `User` implementa `Illuminate\Contracts\Translation\HasLocalePreference` con `preferredLocale()` che ritorna `locale` — Laravel localizza automaticamente le notifiche.

## Sezione 4 — Contenuti DB (dettaglio tecnico)

- Pacchetti: `spatie/laravel-translatable`, `filament/spatie-laravel-translatable-plugin`.
- `Course`, `Video`, `Download`: trait `HasTranslations`, `public $translatable = ['title', 'description']`.
- **Migration di conversione dati:** le colonne `title`/`description` restano TEXT; il contenuto esistente viene riscritto come JSON `{"it": "<valore attuale>"}`. Il `down()` estrae la chiave `it`. Nessuna perdita di dati.
- **Fallback:** locale mancante → si mostra `it` (configurazione fallback del pacchetto).
- **Search** (Livewire `Course/Index` e `Video/Index`):
  `where("title->{$locale}", 'like', "%{$search}%")->orWhere("title->it", 'like', "%{$search}%")` (raggruppate in una closure per non rompere gli altri `when`).
- **Tag nei filtri:** i nomi tag escono nella lingua corrente con fallback it; `withAnyTags` continua a funzionare passando i nomi nella lingua corrente.

## Sezione 5 — Admin Filament

- `SpatieLaravelTranslatablePlugin` registrato sul panel con `->defaultLocales(['it', 'en', 'fr'])`.
- `CourseResource`, `VideoResource`, `DownloadResource`, `TagResource`: trait `Translatable` (resource + pagine List/Create/Edit) → selettore lingua in alto a destra, campi compilabili lingua per lingua.
- L'interfaccia dell'admin resta in italiano.

## Sezione 6 — Test (Pest)

- Middleware `SetLocale`: priorità preferenza utente / sessione / browser, locale non supportato → fallback it.
- Switcher: il cambio lingua persiste su utente e sessione.
- Fallback contenuti: corso senza traduzione EN mostra il titolo IT a un utente EN.
- Search bilingue: utente EN trova un corso cercando il titolo italiano.
- Notifiche: inviate nella lingua preferita del destinatario.

## Fuori ambito (v1)

- Traduzione di `relators` e `additional_resources`.
- Prefisso lingua negli URL / SEO multilingua.
- Traduzione dell'interfaccia admin Filament.
