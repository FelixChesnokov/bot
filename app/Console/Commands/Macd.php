<?php

namespace App\Console\Commands;

use App\Models\Candle;
use Phpml\Classification\SVC;
use Phpml\SupportVectorMachine\Kernel;
use Illuminate\Console\Command;
use Phpml\Classification\KNearestNeighbors;
use Phpml\Regression\LeastSquares;
use Phpml\Regression\SVR;

class Macd extends Command
{
/**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'macd';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'MACD';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

    }

    private function getCandles($from, $to)
    {
        // https://api.binance.com/api/v3/klines?symbol=BTCUSDT&interval=1m
        return Candle::whereBetween('id', [$from, $to])->get()->pluck('open')->toArray();
    }
}
