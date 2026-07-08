<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo \Modules\Frontdeskcrm\Models\MessageTemplate::count() . " templates seeded\n";
foreach (\Modules\Frontdeskcrm\Models\MessageTemplate::all() as $t) {
    echo "  - {$t->event} ({$t->name})\n";
}
