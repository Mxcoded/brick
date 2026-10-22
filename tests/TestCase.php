<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Boot the application for a test.
     *
     * phpunit.xml env overrides (DB_DATABASE=brickspoint_staff_test,
     * MAIL_MAILER=array, CACHE_STORE=array, ...) are only read while the
     * configuration comes from .env. With bootstrap/cache/config.php present
     * they are silently ignored, so the suite would run against the live
     * database -- including migrate:fresh for the RefreshDatabase tests.
     */
    public function createApplication()
    {
        $app = parent::createApplication();

        if ($app->configurationIsCached()) {
            throw new \RuntimeException(
                'Configuration is cached (bootstrap/cache/config.php), which overrides the phpunit.xml env values and would run this suite against the live database. Run `php artisan config:clear` and try again.'
            );
        }

        return $app;
    }
}
