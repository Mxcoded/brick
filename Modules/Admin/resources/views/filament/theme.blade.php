@php
    use App\Support\ThemeSettings;
    use Filament\Support\Colors\Color;

    $theme = ThemeSettings::theme();

    $darkThemes = collect(ThemeSettings::THEMES)
        ->filter(fn (array $meta): bool => $meta['dark'])
        ->keys()
        ->all();
@endphp

<style>
    @foreach (ThemeSettings::THEMES as $slug => $meta)
        html[data-theme="{{ $slug }}"] {
            @foreach (Color::generatePalette($meta['accent']) as $shade => $value)
                --primary-{{ $shade }}: {{ $value }};
            @endforeach
        }
    @endforeach
</style>

<script data-navigate-once>
    (() => {
        const theme = @js($theme);

        document.documentElement.dataset.theme = theme;
        document.documentElement.classList.toggle('dark', @js($darkThemes).includes(theme));
    })();
</script>