<?php

namespace App\Console\Commands;

use App\Services\BinanceService;
use App\Services\IndicatorsService;
use App\Services\PredictService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class TradingCommandOld extends Command
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
        $invervals = ['1m', '15m', '30m', '1h', '4h', '1d'];

        while (true) {
            $t1 = Carbon::now();

            foreach ($invervals as $interval) {
                $indicatorsService = new IndicatorsService();
                $predictService = new PredictService();

                // get last candles
                $binanceService = new BinanceService();
                $candles = $binanceService->getCandles('BTCUSDT', $interval);

                // make MA (moving average)
                $ma = $indicatorsService->ma($candles);

                // predict MA
                $maPredictedValue = $predictService->predict($ma, 10, 3);

                // close prices
                $closePrices = array_column($candles, '4');
                $closePredictedValue = $predictService->predict($closePrices, 10, 3);

                // make MACD
                $macdGisto = $indicatorsService->macdGisto($candles, $closePredictedValue);
                $macdPredictedValue = array_slice($macdGisto, -11);

                $file = file_get_contents('results' . $interval);

                if ($file == '') {
                    $file .= 'time;macd0;macd1;macd2;macd3;macd4;macd5;macd6;macd7;macd8;macd9;macd10;close0;close1;close2;close3;close4;close5;close6;close7;close8;close9;close10;ma0;ma1;ma2;ma3;ma4;ma5;ma6;ma7;ma8;ma9;ma10' . "\n";
                }

                $file .= end($candles)[6] . ';' .
                    $macdPredictedValue[0] . ';' . $macdPredictedValue[1] . ';' . $macdPredictedValue[2] . ';' . $macdPredictedValue[3] . ';' . $macdPredictedValue[4] . ';' . $macdPredictedValue[5] . ';' . $macdPredictedValue[6] . ';' . $macdPredictedValue[7] . ';' . $macdPredictedValue[8] . ';' . $macdPredictedValue[9] . ';' . $macdPredictedValue[10] . ';' .
                    $closePredictedValue[0] . ';' . $closePredictedValue[1] . ';' . $closePredictedValue[2] . ';' . $closePredictedValue[3] . ';' . $closePredictedValue[4] . ';' . $closePredictedValue[5] . ';' .$closePredictedValue[6] . ';' . $closePredictedValue[7] . ';' . $closePredictedValue[8] . ';' . $closePredictedValue[9] . ';' . $closePredictedValue[10] . ';' .
                    $maPredictedValue[0] . ';' . $maPredictedValue[1] . ';' . $maPredictedValue[2] . ';' . $maPredictedValue[3] . ';' . $maPredictedValue[4] . ';' . $maPredictedValue[5] . ';' . $maPredictedValue[6] . ';' . $maPredictedValue[7] . ';' . $maPredictedValue[8] . ';' .$maPredictedValue[9] . ';' . $maPredictedValue[10] .
                    "\n";

                file_put_contents('results' . $interval, $file);
            }

            $t2 = Carbon::now();
            $wait = floor(abs(30 - $t2->diffInMilliseconds($t1)/1000));
            sleep($wait);
        }
    }
}
