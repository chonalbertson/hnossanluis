<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Programación de tareas operativas, antimalware y cumplimiento de retención de datos (Sección 12)
Schedule::command('sanluis:backup')->dailyAt('02:00');
Schedule::command('sanluis:scan-malware --type=scheduled_scan')->dailyAt('03:00');
Schedule::command('sanluis:prune-data')->weekly();
