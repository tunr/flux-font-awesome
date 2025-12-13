# Flux Font Awesome

This project uses [Flux Font Awesome](https://github.com/tunr/flux-font-awesome) to integrate Font Awesome icons with Flux UI.

## Icon Naming

All Font Awesome icons use the `fa-` prefix:
- `fa-user`, `fa-home`, `fa-github`

## Usage

```blade
{{-- Component syntax --}}
<flux:icon.fa-user />

{{-- Name attribute syntax --}}
<flux:icon name="fa-user" />

{{-- Inside Flux components --}}
<flux:button icon="fa-user">Profile</flux:button>
<flux:navlist.item icon="fa-home" href="/">Home</flux:navlist.item>
```

## Styles

Use the `style` attribute to switch between Font Awesome styles:

```blade
<flux:icon.fa-user style="solid" />    {{-- Default, filled --}}
<flux:icon.fa-user style="regular" />  {{-- Outlined --}}
<flux:icon.fa-github style="brands" /> {{-- Brand icons --}}
```

Pro styles (if installed): `light`, `thin`, `duotone`

## Sizing

```blade
<flux:icon.fa-user size="sm" />  {{-- size-5 --}}
<flux:icon.fa-user size="md" />  {{-- size-6 (default) --}}
<flux:icon.fa-user size="lg" />  {{-- size-8 --}}

{{-- Or use Tailwind classes --}}
<flux:icon.fa-user class="size-8" />
```

## Building New Icons

Before using an icon, it must be built:

```bash
php artisan flux-fa:build user home github
```

Brand icons (github, twitter, etc.) are auto-detected and built with only the `brands` style.

## Commands

| Command | Purpose |
|---------|---------|
| `flux-fa:build <icons>` | Build icon components |
| `flux-fa:rebuild` | Rebuild all existing icons |
| `flux-fa:list` | List built icons |
| `flux-fa:status` | Check installation status |
