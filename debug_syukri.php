<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$participant = \App\Models\Participant::where('name', 'like', '%Syukri%')->first();
if ($participant) {
    echo "ID: " . $participant->id . "\n";
    echo "Name: " . $participant->name . "\n";
    echo "Custom Date: " . ($participant->custom_date ? $participant->custom_date->format('Y-m-d') : 'NULL') . "\n";
    echo "Event ID: " . $participant->event_id . "\n";
    if ($participant->event) {
        echo "Event Name: " . $participant->event->name . "\n";
        echo "Event Start Date: " . ($participant->event->start_date ? $participant->event->start_date->format('Y-m-d') : 'NULL') . "\n";
    }
}
else {
    echo "Participant not found.\n";
}
