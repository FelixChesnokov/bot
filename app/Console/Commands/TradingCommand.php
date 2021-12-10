<?php

namespace App\Console\Commands;

use App\Models\Candle;
use App\Services\BinanceService;
use App\Services\IndicatorsService;
use App\Services\PredictService;
use GuzzleHttp\Client;
use Illuminate\Console\Command;

class TradingCommand extends Command
{
/**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'trading';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start trading';

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
        // get last candles
        $binanceService = new BinanceService();
        $candles = $binanceService->getCandles('BTCUSDT', '15m');


        // make MACD
        $indicatorsService = new IndicatorsService();
        $macdGisto = $indicatorsService->macdGisto($candles);

        // predict MACD
        $predictService = new PredictService();
        $macdPredictedValue = $predictService->predictMacd($macdGisto, 2);


        // make MA (moving average)
        $ma = $indicatorsService->ma($candles);

        die(var_dump($ma));

        // predict MA
//        $maPredictedValue = $predictService->predictMacd($ma, 2);


        // difference between MA and current price


    }
}
