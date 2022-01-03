<?php

namespace App\Console;

use App\Console\Commands\ImportCsvData;
use App\Console\Commands\BasicBot\TradingCommand1 as BasicBotTradingCommand1;
use App\Console\Commands\SaveBasicBot\TradingCommand1 as SaveBasicBotTradingCommand1;
use App\Console\Commands\Optimized\TradingCommand1 as OptimizedBotTradingCommand1;
use App\Console\Commands\TradingCommandExample;
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
        TradingCommandExample::class,

        // BasicBot
        BasicBotTradingCommand1::class,

        // SaveBasicBot
        SaveBasicBotTradingCommand1::class,

        // OptimizedBot
        OptimizedBotTradingCommand1::class,


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
