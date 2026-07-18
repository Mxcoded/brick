<?php

use Illuminate\Contracts\Console\Kernel;
use Modules\Frontdeskcrm\Models\MessageTemplate;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

echo MessageTemplate::count()." templates seeded\n";
foreach (MessageTemplate::all() as $t) {
    echo "  - {$t->event} ({$t->name})\n";
}
