<?php

namespace App\Console\Commands;

use App\Services\BinanceService;
use App\Services\IndicatorsService;
use App\Services\PredictService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use LupeCode\phpTraderNative\Trader;

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
//        $invervals = ['1m', '15m', '30m', '1h', '4h', '1d'];
        $invervals = ['5m']; //5m
        $isBought = false;
        $boughtTime = null;
        $status = [
            'time' => null,
            'type' => null
        ];
        $coins = 0;
        $money = 100;

        $rsiTop = 80; //85 or 80
        $rsiBottom = 20; // 30 or 20

        while (true) {
            $t1 = Carbon::now();

            foreach ($invervals as $interval) {
                // get last candles
                $binanceService = new BinanceService();
                $candles = $binanceService->getCandles('BTCUSDT', $interval);
                $openPrices = array_column($candles, '1');
                $closePrices = array_column($candles, '4');


                $lastCandleOpen = array_slice($openPrices, -2, 1)[0];
                $lastCandleClose = array_slice($closePrices, -2, 1)[0];
                $rsi = Trader::rsi($closePrices, 6);
                $bbands = Trader::bbands($closePrices, 21);

                $lastRsi = array_slice($rsi, -2, 1)[0];
                $lastBbands = [
                    'UpperBand' => array_slice($bbands['UpperBand'], -2, 1)[0],
                    'MiddleBand' => array_slice($bbands['MiddleBand'], -2, 1)[0],
                    'LowerBand' => array_slice($bbands['LowerBand'], -2, 1)[0]
                ];

                if($lastCandleOpen > $lastCandleClose) {
                    $candleTopLine = $lastCandleOpen;
                    $candleBottomLine = $lastCandleClose;
                } else {
                    $candleTopLine = $lastCandleClose;
                    $candleBottomLine = $lastCandleOpen;
                }

                $price = end($closePrices);

                // buy
                if($lastRsi <= $rsiBottom && $lastBbands['LowerBand'] >= $candleBottomLine) {
                    if($money >= 25) {
                        $coins += 25/$price;
                        $money = $money - 25;

                        $status = [
                            'time' => Carbon::now()->timestamp,
                            'type' => 'bought'
                        ];
                    } else {
                        $status = [
                            'time' => Carbon::now()->timestamp,
                            'type' => 'can not bought'
                        ];
                    }
                }

                //sell
                if($lastRsi >= $rsiTop && $lastBbands['UpperBand'] <= $candleTopLine) {
                    if($coins > 0) {
                        $money += $coins * $price;
                        $coins = 0;

                        $status = [
                            'time' => Carbon::now()->timestamp,
                            'type' => 'sell'
                        ];
                    }
                }

                $file = file_get_contents('results' . $interval);
                $file .= $status['time'] . ';' . $status['type'] . ';' . $price . ';' . $money . ';' . $coins . "\n";
                file_put_contents('results' . $interval, $file);
            }


            $t2 = Carbon::now();
            $wait = floor(abs(30 - $t2->diffInMilliseconds($t1) / 1000));
            sleep($wait);
        }
    }
}
