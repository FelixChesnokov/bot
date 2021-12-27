<?php

namespace App\Console;

use App\Console\Commands\ImportCsvData;
use App\Console\Commands\Macd;
use App\Console\Commands\NeuralNetwork;
use App\Console\Commands\TradingCommand;
use Illuminate\Console\Scheduling\Schedule;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        ImportCsvData::class,
        NeuralNetwork::class,
        Macd::class,
        TradingCommand::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {

    }
}
