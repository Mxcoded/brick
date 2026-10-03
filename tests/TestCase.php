<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->guardAgainstTestingAgainstTheDevelopmentDatabase();
    }

    /**
     * Fail loudly if the suite is pointed at the development schema.
     *
     * A few suites use RefreshDatabase, which runs migrate:fresh. That is
     * harmless against the test schema and catastrophic against the real one, so
     * this refuses to run rather than let the connection be decided by whichever
     * .env happened to load.
     *
     * The trap is a cached config: `php artisan config:cache` writes
     * bootstrap/cache/config.php with app.env and the database name baked in, and
     * a cached config wins over .env.testing. That silently turns the suite loose
     * on the development database - and it also disables Filament's fillForm()
     * test helper, which fails in a much more confusing way. `php artisan
     * optimize:clear` is the fix; this assertion is the alarm.
     */
    protected function guardAgainstTestingAgainstTheDevelopmentDatabase(): void
    {
        $expected = config('brickspoint.testing_database');

        if (! $expected) {
            return;
        }

        $actual = config('database.connections.'.config('database.default').'.database');

        if ($actual !== $expected) {
            $this->fail(
                "Refusing to run: the test suite is connected to '{$actual}' but "
                ."'.env.testing' expects '{$expected}'.\n"
                .'A cached config is the usual cause - run: php artisan optimize:clear'
            );
        }
    }
}
