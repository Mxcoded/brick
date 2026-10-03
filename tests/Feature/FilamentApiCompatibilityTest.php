<?php

namespace Tests\Feature;

use Filament\Forms\Components\TextInput;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * The installed Filament is v5, and v5 moved and deleted a lot of the v3 API.
 *
 * These failures are nasty for a specific reason: they do not happen when the
 * class is autoloaded, they happen when the offending call is finally reached.
 * A relation manager is a lazily-booted Livewire component, so a stale import
 * inside one sits dormant until somebody clicks that tab, and a page-render
 * test over an empty table walks straight past it. That is how
 * `Filament\Tables\Actions\Action` and `TextInput::money()` both reached
 * production behind a fully green suite.
 *
 * So this guard reads the source instead of exercising it. It is deliberately
 * app-wide rather than per-module: the version boundary applies to every module
 * equally, and a guard that only covered Restaurant would have let the same
 * import sit broken in Tasks.
 */
class FilamentApiCompatibilityTest extends TestCase
{
    /**
     * Modules that ship a Filament surface. Listed rather than globbed so that
     * a module gaining Filament code without joining the guard is a visible
     * omission rather than a silent pass.
     *
     * @var array<int, string>
     */
    private const MODULES = ['Admin', 'Finance', 'Frontdeskcrm', 'Restaurant', 'Tasks'];

    #[Test]
    public function every_module_with_filament_code_is_covered_by_this_guard(): void
    {
        $withFilament = [];

        foreach (glob(base_path('Modules/*/app/Filament'), GLOB_ONLYDIR) ?: [] as $directory) {
            $withFilament[] = basename(dirname($directory, 2));
        }

        sort($withFilament);

        $this->assertSame(
            self::MODULES,
            $withFilament,
            'A module now has app/Filament. Add it to this guard so its imports get checked.'
        );
    }

    #[Test]
    public function no_filament_import_points_at_a_class_that_does_not_exist(): void
    {
        $missing = [];

        foreach ($this->filamentFiles() as $file) {
            $contents = (string) file_get_contents($file);

            if (! preg_match_all(
                '/^use\s+(Filament\\\\[^\s;]+)(?:\s+as\s+(\w+))?\s*;/mi',
                $contents,
                $found,
                PREG_SET_ORDER
            )) {
                continue;
            }

            $code = $this->executableCode($contents);

            foreach ($found as $use) {
                $import = $use[1];
                // The optional `as` group is absent entirely when unmatched.
                $alias = $use[2] ?? null;

                if (class_exists($import) || interface_exists($import) || trait_exists($import) || enum_exists($import)) {
                    continue;
                }

                $short = $alias ?: substr((string) strrchr('\\'.$import, '\\'), 1);

                // An unresolvable import is only a *missing class* if the file
                // uses it as a class. `use Filament\Actions;` is a namespace
                // alias written Actions\Action::make(), and that is legitimate.
                if (preg_match('/\b'.preg_quote($short, '/').'\s*\\\\/', $code)) {
                    continue;
                }

                $missing[] = $this->rel($file).' → '.$import;
            }
        }

        $this->assertSame(
            [],
            $missing,
            "These Filament classes are not installed. v5 moved most of the v3 API:\n".implode("\n", $missing)
        );
    }

    #[Test]
    public function the_installed_text_input_really_has_no_money_helper(): void
    {
        // Guards the premise of the scan below. If a future Filament restores
        // money(), this test says so and the scan should be revisited.
        $this->assertFalse(
            method_exists(TextInput::class, 'money'),
            'TextInput::money() is back; drop the scan and use it directly.'
        );
    }

    #[Test]
    public function no_text_input_calls_money_which_only_survived_on_columns_and_entries(): void
    {
        $offenders = [];

        foreach ($this->filamentFiles() as $file) {
            $code = $this->executableCode((string) file_get_contents($file));

            if (! preg_match_all('/->\s*money\s*\(/', $code, $calls, PREG_OFFSET_CAPTURE)) {
                continue;
            }

            foreach ($calls[0] as [$match, $offset]) {
                // money() still exists on TextColumn and TextEntry, so the only
                // fatal combination is a TextInput chain.
                if ($this->rootComponent($code, $offset) === 'TextInput') {
                    $offenders[] = $this->rel($file);
                }
            }
        }

        $this->assertSame(
            [],
            array_values(array_unique($offenders)),
            "TextInput has no money() in this Filament version. Use ->numeric()->prefix('NGN'):\n"
                .implode("\n", array_unique($offenders))
        );
    }

    /**
     * The component a method chain hangs off, by walking back to the nearest
     * `Something::make(`. Comments and strings are already stripped, so this
     * only ever sees real code.
     */
    private function rootComponent(string $code, int $offset): ?string
    {
        $before = substr($code, 0, $offset);

        if (! preg_match_all('/([a-zA-Z_][a-zA-Z0-9_]*)::make\(/', $before, $found, PREG_OFFSET_CAPTURE)) {
            return null;
        }

        return end($found[1])[0];
    }

    private function executableCode(string $contents): string
    {
        return (string) preg_replace(
            [
                '#/\*.*?\*/#s',
                '#//[^\n]*#',
                '#^\s*\*.*$#m',
                '#\'(?:[^\'\\\\]|\\\\.)*\'#s',
                '#"(?:[^"\\\\]|\\\\.)*"#s',
            ],
            '',
            $contents
        );
    }

    private function rel(string $file): string
    {
        return str_replace(base_path().DIRECTORY_SEPARATOR, '', $file);
    }

    /**
     * @return array<int, string>
     */
    private function filamentFiles(): array
    {
        $files = [];

        foreach (self::MODULES as $module) {
            $root = module_path($module, 'app/Filament');

            if (! is_dir($root)) {
                continue;
            }

            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS)
            );

            /** @var \SplFileInfo $file */
            foreach ($iterator as $file) {
                if ($file->getExtension() === 'php') {
                    $files[] = $file->getPathname();
                }
            }
        }

        return $files;
    }
}
