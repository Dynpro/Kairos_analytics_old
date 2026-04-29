<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    private function phmGeneratorDriver(): string
    {
        return strtolower((string) env('PHM_GENERATOR_DRIVER', 'php'));
    }

    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        \App\Console\Commands\ParentPhmCron::class,
        \App\Console\Commands\PHMCron::class,
        \App\Console\Commands\PHMDataCron::class,
        \App\Console\Commands\DownloadPdfCron::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('ParentPhm:cron')
                ->dailyAt('3:00');

        if ($this->phmGeneratorDriver() === 'php') {
            $schedule->command('phm:cron')
                    ->everyMinute();
            $schedule->command('phmdata:cron')
                    ->everyMinute();
            $schedule->command('downloadPdf:cron')
                    ->everyMinute();
        }
    }
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
