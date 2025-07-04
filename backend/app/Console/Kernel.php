<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule)
    {
        // // Chạy một lần lúc 14:25 hôm nay tesst
        // $schedule->command('reports:generate')->when(function () {
        //     return now()->format('Y-m-d H:i') === '2025-06-29 14:37';
        // });

        // Các lịch khác nếu vẫn cần giữ
        $schedule->command('reports:generate')->dailyAt('13:55');
        $schedule->command('reports:generate')->monthlyOn(1, '00:00');
        $schedule->command('reports:generate')->yearlyOn(1, 1, '00:00');
    }


    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}