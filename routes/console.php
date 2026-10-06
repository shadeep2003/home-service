<?php
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
Artisan::command('auth:prune-login-challenges', function () {
    $count = \App\Models\LoginChallenge::where('pending_until', '<', now()->subDay())->delete();
    $this->info("Removed {$count} expired login challenges.");
})->purpose('Delete login challenges older than one day');
Schedule::command('auth:prune-login-challenges')->daily();
