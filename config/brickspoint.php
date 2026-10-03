<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Test Database Guard
    |--------------------------------------------------------------------------
    |
    | The schema the suite is allowed to touch. A handful of suites use
    | RefreshDatabase, which runs migrate:fresh and therefore wipes whatever
    | schema is configured. Pinning the name here lets tests/TestCase refuse to
    | run at all if the connection has been pointed somewhere else, which is
    | what happens when a cached config wins over .env.testing.
    |
    */

    'testing_database' => env('TESTING_DB_DATABASE', 'brickspoint_staff_test'),

];
