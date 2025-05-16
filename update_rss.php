<?php

// Bootstrap Laravel application
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

// Run the command
$status = $kernel->call('feeds:fetch');

// Output status
if ($status === 0) {
    echo "RSS feeds updated successfully at " . date('Y-m-d H:i:s') . "\n";
} else {
    echo "Error updating RSS feeds at " . date('Y-m-d H:i:s') . "\n";
}

// Terminate the application
$kernel->terminate(
    \Illuminate\Http\Request::capture(),
    new \Illuminate\Http\Response()
);
