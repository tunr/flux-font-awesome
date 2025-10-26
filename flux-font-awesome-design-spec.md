# Flux Font Awesome — Design Spec

## 1) Purpose

Deliver Font Awesome (FA) SVG icons as **Flux UI–compatible** icons:

- Use the **standard Flux icon tag** with the **FA variant as an attribute**:
  ```blade
  <flux:icon.fa.user />                    {{-- uses default variant from config, e.g., "solid" --}}
  <flux:icon.fa.user variant="regular" />  {{-- override per usage --}}
  ```
- **One Blade file per icon** (e.g., `fa/user.blade.php`) that contains all the SVG paths for the icon’s imported variants and selects at runtime.  
- **No FA CSS**. Provide a tiny inline CSS shim for `spin="pulse"`.

> Reference: Flux “Custom Icons” docs show how custom icons resolve in `resources/views/flux/icon/...`.

---

## 2) Public API (DX)

### 2.1 Usage

```blade
{{-- Defaults to config('flux-fa.default_variant') e.g., "solid" --}}
<flux:icon.fa.user />

{{-- Switch variant via attribute --}}
<flux:icon.fa.user variant="regular" />

{{-- FA behavior props (unchanged) --}}
<flux:icon.fa.user spin />
<flux:icon.fa.user spin="pulse" size="2x" rotate="90" flip="horizontal" fixed inverse />

{{-- a11y --}}
<flux:icon.fa.user title="User account" />
```

**Supported `variant` values** (configurable): `solid`, `regular`, `light`, `thin`, `duotone`, `brands`.  
**Default**: `config('flux-fa.default_variant')`.

> Note on naming: `variant` means **FA style** here. Flux sizing presets use a different prop (`fluxVariant`) to avoid collision (see Base Component).

---

## 3) File Layout (Single-file icons)

```
resources/
  views/
    flux/
      icon/
        fa/
          user.blade.php      ← one file, contains all available variants for "user"
          gear.blade.php
          github.blade.php
          ...
    components/
      fa/
        base.blade.php        ← shared wrapper (svg, classes, css injection)
```

- Each `fa/{icon}.blade.php` file internally switches on the **FA variant attribute** at runtime and emits only that variant’s paths.
- If an icon only exists in one or a subset of variants, the file contains only those branches; the resolver falls back or error-notes as configured.

---

## 4) Rendering Design

### 4.1 Base Component (`resources/views/components/fa/base.blade.php`)

- Props (**Flux sizing presets** moved to `fluxVariant`):
  - `fluxVariant` (default from `config('flux-fa.flux_size_default', 'outline')`), **not** FA style.
  - FA props: `spin` (`true|'pulse'`), `rotate` (`0|90|180|270`), `flip` (`'horizontal'|'vertical'|'both'`), `fixed` (bool), `inverse` (bool), `size` (`'xs'|'sm'|'lg'|'xl'|'2x'|'3x'|'4x'|'5x'`), `title` (string).
- Behavior:
  - Builds classes using `Flux::classes()` (fallback to string concat if Flux not present).
  - Injects tiny CSS once if enabled.
  - Applies `fill="currentColor"` and `viewBox` from config.

**Key points (shape):**
```blade
@props([
  'fluxVariant' => config('flux-fa.flux_size_default', 'outline'),
  'spin' => false, 'rotate' => 0, 'flip' => null, 'fixed' => false,
  'inverse' => false, 'size' => null, 'title' => null,
])

{{-- inject minimal CSS once if enabled --}}
{{-- map FA sizes, apply spin/pulse/rotate/flip/fixed/inverse, apply fluxVariant size presets --}}
<svg ... fill="currentColor" viewBox="{{ config('flux-fa.view_box') }}">
  {{ $slot }}
</svg>
```

### 4.2 Icon Blade file (per icon, single file)

**Generated example:** `resources/views/flux/icon/fa/user.blade.php`

```blade
@php
    /** Resolve FA variant: requested attribute → alias map → default */
    $requested = $attributes->get('variant');
    $variant = \Nate\FluxFontAwesome\Support\Variant::resolve($requested, 'user'); // second param is icon name

    // Helper closures hold the inner SVG chunks for each available variant.
@endphp

@switch($variant)
    @case('solid')
        <x-fa.base {{ $attributes }}>
            {{-- SOLID paths for "user" --}}
            <path d="M224 256A128 128 0 1 0 224 0a128 128 0 1 0 0 256zM313.6 288h-19.1..." />
        </x-fa.base>
        @break

    @case('regular')
        <x-fa.base {{ $attributes }}>
            {{-- REGULAR paths for "user" (if available) --}}
            <path d="..." />
        </x-fa.base>
        @break

    @case('brands')
        <x-fa.base {{ $attributes }}>
            {{-- BRANDS paths for "user" (rare; example icon would be github/x, not user) --}}
            <path d="..." />
        </x-fa.base>
        @break

    @case('duotone')
        <x-fa.base {{ $attributes }}>
            {{-- DUOTONE (Pro): secondary layer first with configurable opacity --}}
            <path d="..." opacity="{{ config('flux-fa.duotone.secondary_opacity') }}" />
            <path d="..." />
        </x-fa.base>
        @break

    @default
        {{-- Fallback (optional): render default variant if requested not available --}}
        @php $fallback = config('flux-fa.default_variant'); @endphp
        @includeFirst([
            "flux.icon.fa._partials.user.$variant",   // if you ever add partials
            "flux.icon.fa._partials.user.$fallback",  // or fallback
        ], ['attributes' => $attributes])
@endswitch
```

- The **generator** fills in only the `@case` blocks for the variants found in sources; missing variants are omitted.  
- You can choose strict behavior (throw helpful exception if requested variant doesn’t exist) or gentle fallback (render default). This is configurable.

---

## 5) Variant Resolution

`Nate\FluxFontAwesome\Support\Variant::resolve(?string $requested, string $icon): string`

Resolution order:
1. If provided, map `requested` through `variant_aliases` (config).
2. If `requested` is in `valid_variants` **and exists** for this icon → return it.
3. Else if `icon_defaults[$icon]` exists → return that.
4. Else return `config('flux-fa.default_variant')`.

Optionally, log a notice in console/testing if a requested variant doesn’t exist for the icon.

---

## 6) Config

`config/flux-fa.php`

```php
return [
    // Locations to read FA SVGs (dev installs Free; Pro is opt-in)
    'sources' => [
        'free' => base_path('node_modules/@fortawesome/fontawesome-free/svgs'),
        // 'pro' => base_path('node_modules/@fortawesome/fontawesome-pro/svgs'),
        // 'custom' => base_path('resources/icons/fa'),
    ],

    // Where to write single-file icons
    'target' => resource_path('views/flux/icon/fa'),

    // FA style default used when <flux:icon.fa.user /> has no variant
    'default_variant' => 'solid',

    // Valid FA style values (resolver checks against this list)
    'valid_variants' => ['solid','regular','light','thin','duotone','brands'],

    // Optional aliases (DX niceties)
    'variant_aliases' => [
        'brand' => 'brands',
    ],

    // Optional per-icon defaults (e.g., brands for github)
    'icon_defaults' => [
        // 'github' => 'brands',
    ],

    // Flux sizing preset default for <x-fa.base> (NOT FA style)
    'flux_size_default' => 'outline', // 'outline'|'solid'|'mini'|'micro'

    // SVG viewport
    'view_box' => '0 0 512 512',

    // FA size tokens to Tailwind utilities
    'size_map' => [
        'xs'=>'size-3.5','sm'=>'size-4','lg'=>'size-5','xl'=>'size-6',
        '2x'=>'size-8','3x'=>'size-10','4x'=>'size-12','5x'=>'size-16',
    ],

    // Inject tiny CSS once (for spin="pulse")
    'auto_inject_css' => true,

    // Duotone (Pro)
    'duotone' => [
        'secondary_opacity' => 0.4,
        'swap_order' => false,
    ],

    // Behavior when requested variant missing for icon: 'fallback' | 'error'
    'missing_variant' => 'fallback',
];
```

---

## 7) CLI (Artisan)

> All commands produce single-file icons with variant branches.

### 7.1 `fa:install`
- Publishes config + base component.
- Echoes quickstart usage (attribute variants).
- Notes the Flux custom icons doc URL.

### 7.2 `fa:build`
- Options:
  - `--set=solid,regular,brands,...`
  - `--icons=user,gear,github`
  - `--match=book*` or `--all`
  - `--refresh` (reparse and regenerate)
  - `--source=free|pro|custom` (override source)
  - `--manifest=path` (optional curated list)
- For each icon name:
  1) Collect available SVGs across chosen sets (variants).
  2) Parse each SVG to **inner children**.
  3) **Write or update one Blade file** with the `@switch`/`@case` blocks for those variants.
  4) If file exists, update only changed branches to avoid noisy diffs.
- Summary: added/updated/skipped with helpful guidance.

### 7.3 `fa:add`
- Add a **single icon** from a raw SVG (file or STDIN) into **one icon file**, creating/updating the `@case($variant)` block.

### 7.4 `fa:prune`
- With a manifest: remove **variant branches** in files that aren’t listed; if a file ends up with zero branches, delete the file.
- Without manifest: safe mode—only delete empty icon files (confirm with `--force`).

---

## 8) Parsing & Writing

- **SvgParser**
  - Strip outer `<svg>`; preserve children nodes (`<path>`, `<g>`, `<defs>`, `<mask>`, etc.).
  - Remove `width/height/xmlns/style`.
  - Keep node-level `fill/opacity` (outer `<svg>` sets `currentColor`).
  - Duotone: detect primary/secondary; apply configured `secondary_opacity`.

- **IconWriter (single-file)**
  - Create or update `fa/{icon}.bl ade.php`:
    - Ensure `@case('<variant>')` sections exist for each parsed variant.
    - Remove sections for variants no longer present **only** on `fa:prune` or `--refresh --prune-missing`.
    - Preserve section order = order in `valid_variants`.
  - Idempotent: skip rewriting identical sections.

- **Manifest (optional)**
  - `{ "user": ["solid","regular"], "github": ["brands"] }`.

---

## 9) Minimal CSS (no FA CSS)

Inline (once per request), or via Blade directive if you ever want it in layouts:

```css
@keyframes fa-step-rotate { from { transform: rotate(0deg) } to { transform: rotate(360deg) } }
.fa-animate-pulse { animation: fa-step-rotate 1s steps(8) infinite; transform-origin: center; display:inline-block }
```

---

## 10) Laravel Package Best Practices (confirmed)

- Auto-discovered Service Provider; `mergeConfigFrom` in `register()`.
- Tagged publishes: `flux-fa-config`, `flux-fa-views` (for base).
- Console-only command registration.
- No `env()` in runtime; only `config()` (works with `config:cache`).
- Testbench for tests; Pint + (Lara)Stan for quality.
- PHP 8.2+; Laravel 10/11 CI matrix.
- Clear, actionable CLI errors and idempotent file writes.
- **Graceful Flux absence**: component still renders with class strings if `Flux::classes()` unavailable.

---

## 11) Repo Scaffolding

**Repo:** `flux-font-awesome` (MIT)

Include:
- `LICENSE` (MIT), `README.md` (quick start, Flux docs link, examples), `CHANGELOG.md`, `CONTRIBUTING.md`, `CODE_OF_CONDUCT.md`, `SECURITY.md`
- `.editorconfig`, `.gitignore`, `.gitattributes` (export-ignore `/tests`, `/.github`)
- `composer.json` (PSR-4, scripts: test/analyse/format)
- `phpunit.xml`, `phpstan.neon`, `pint.json`
- `.github/workflows/ci.yml` with PHP 8.2/8.3 × Laravel 10/11

**Tests**
- Unit: Variant resolver; SvgParser; IconWriter (branch add/update/remove).
- Feature: Build/Add/Prune; Render `<flux:icon.fa.user variant="...">` → correct case; FA props → correct classes.

---

## 12) Security & Licensing

- Pro sources opt-in; CLI prints clear reminders not to commit Pro icons to public repos.
- No outbound network calls.

---

## 13) Hand-off Checklist

- [ ] Implement ServiceProvider, config publishing, minimal CSS directive (optional).
- [ ] Implement `Variant::resolve()`.
- [ ] Implement `MinimalCss`, `SvgParser`, `IconWriter` (single-file, multi-variant), `Manifest`.
- [ ] Implement commands: `fa:install`, `fa:build`, `fa:add`, `fa:prune`.
- [ ] Generate/publish `components/fa/base.blade.php`.
- [ ] Write tests + CI + README (include Flux custom icon docs URL).
- [ ] Tag `v0.1.0`.

---

If you want **strict missing-variant behavior** (throw instead of fallback), set `missing_variant => 'error'`. Want me to add example code for the single-file `IconWriter` (how it inserts/replaces `@case` blocks safely)?