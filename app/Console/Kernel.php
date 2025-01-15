<?php

namespace App\Console;
use App\Http\Controllers\Api\UserStreakController;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->call(function () {
            $userStreakService = app(UserStreakController::class);
            $userStreakService->resetInactiveStreaks();
        })->dailyAt('23:59'); // Executes daily at 11:59 PM
    }



    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
