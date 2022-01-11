<?php

namespace App\Console\Commands;

use App\Services\BinanceService;
use App\Services\TelegramService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use LupeCode\phpTraderNative\Trader;

class TradingCommandExample2 extends Command
{
    private $period = '1m';
    private $symbol = 'BTCUSDT';
    private $filename = 'trading_basic_1_command_results';

    private $rsiPeriod = 6;
    private $rsiTop = 70;
    private $rsiBottom = 30;

    private $bbandsPeriod = 21;

    private $buyValue = 0.1;
    private $money = 100;

    private $updateUpperBbands = 1;
    private $updateLowerBbands = 1;

    protected $signature = 'trading_basic_1';

    protected $description = 'Start trading basic satting 1m';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $status = [
            'time' => null,
            'type' => null
        ];
        $money = $this->money;
        $coins = 0;
        $buyActions = [];
        $lastBuyTime = null;
        $longPositionOpen = null;
        $shortPositionOpen = null;

        while (true) {
            $t1 = Carbon::now();

            // check trend and update settings value
//            $binanceService5m = new BinanceService();
//            $binanceService5m->getCandles($this->symbol, '5m');
//            $closePrices5m = $binanceService5m->getClosePrices();
//            $bbands5m = array_slice(Trader::bbands($closePrices5m, $this->bbandsPeriod)['MiddleBand'], -10);
//
//            if ($bbands5m[0] < end($bbands5m)) {
//                // up
//                $this->updateUpperBbands = 1.002;
////                $this->updateLowerBbands = 1.002;
//            } else {
//                // down
//                $this->updateUpperBbands = 0.998;
////                $this->updateLowerBbands = 0.998;
//            }

            // get candles
            $binanceService = new BinanceService();
            $binanceService->getCandles($this->symbol, $this->period);

            // get open close prices
            $openPrices = $binanceService->getOpenPrices();
            $closePrices = $binanceService->getClosePrices();

            // get last open close prices
            $lastCandleOpen = $binanceService->getPenultimate($openPrices);
            $lastCandleClose = $binanceService->getPenultimate($closePrices);

            // build indicators
            $rsi = Trader::rsi($closePrices, $this->rsiPeriod);
            $bbands = Trader::bbands($closePrices, $this->bbandsPeriod);

            // get penultimate value of indicators
            $lastRsi = $binanceService->getPenultimate($rsi);
            $lastBbands = [
                'UpperBand' => $binanceService->getPenultimate($bbands['UpperBand']) * $this->updateUpperBbands,
                'LowerBand' => $binanceService->getPenultimate($bbands['LowerBand']) * $this->updateLowerBbands,
            ];

            // get levels of last closed candles
            $candleTopLine = max($lastCandleOpen, $lastCandleClose);
            $candleBottomLine = min($lastCandleOpen, $lastCandleClose);

            // get last price
            $price = end($closePrices);

            // check liquidation
//            $this->checkLiquidation($buyPrices, count($buyActions), $price);

            // check last buy time
            $candleTimestamps = $binanceService->getTimestamps();
            if ($lastBuyTime && $lastBuyTime == $binanceService->getPenultimate($candleTimestamps, -3)) {
                $lastBuyTime = null;
            }




            /**
             * MAIN BOT LOGIC START
             */
            //sell LONG
            if ($lastRsi >= $this->rsiTop && $lastBbands['UpperBand'] <= $candleTopLine) {
                if ($coins > 0 && $longPositionOpen) {
                    $buyResult = $binanceService->sell($money, $coins, $price, count($buyActions), $buyActions, 'Long');
                    $money = $buyResult['money'];
                    $coins = $buyResult['coins'];
                    $buyActions = [];
                    $longPositionOpen = null;

                    $status = $this->sendStatus($buyResult['status'], $money, $coins);
                }
            }

            //sell SHORT
            if ($lastRsi <= $this->rsiBottom && $lastBbands['LowerBand'] >= $candleBottomLine) {
                if ($coins > 0 && $shortPositionOpen) {
                    $buyResult = $binanceService->sell($money, $coins, $price, count($buyActions), $buyActions, 'Short');
                    $money = $buyResult['money'];
                    $coins = $buyResult['coins'];
                    $buyActions = [];
                    $shortPositionOpen = null;

                    $status = $this->sendStatus($buyResult['status'], $money, $coins);
                }
            }

            // buy LONG
            if ($lastRsi <= $this->rsiBottom && $lastBbands['LowerBand'] >= $candleBottomLine) {
                if ($lastBuyTime == null && $money > 2) {
                    $currentBuyValue = min($money - 1, $this->money * $this->buyValue * (count($buyActions) + 1));

                    $buyResult = $binanceService->buy($money, $coins, $price, $currentBuyValue, 'Long');
                    $money = $buyResult['money'];
                    $coins = $buyResult['coins'];
                    $buyActions[] = [
                        'price' => $price,
                        'value' => $currentBuyValue,
                        'coins' => $buyResult['coinsByAction']
                    ];
                    $lastBuyTime = end($candleTimestamps);
                    $longPositionOpen = true;

                    $status = $this->sendStatus($buyResult['status'], $money, $coins);
                }
            }

            // buy SHORT
            if ($lastRsi >= $this->rsiTop && $lastBbands['UpperBand'] <= $candleTopLine) {
                if ($lastBuyTime == null && $money > 2) {
                    $currentBuyValue = min($money - 1, $this->money * $this->buyValue * (count($buyActions) + 1));

                    $buyResult = $binanceService->buy($money, $coins, $price, $currentBuyValue, 'Short');
                    $money = $buyResult['money'];
                    $coins = $buyResult['coins'];
                    $buyActions[] = [
                        'price' => $price,
                        'value' => $currentBuyValue,
                        'coins' => $buyResult['coinsByAction']
                    ];
                    $lastBuyTime = end($candleTimestamps);
                    $shortPositionOpen = true;

                    $status = $this->sendStatus($buyResult['status'], $money, $coins);
                }
            }
            /**
             * MAIN BOT LOGIC END
             */


            // update money
//            $this->money = $money;

            // save data to the file
            if ($status['time']) {
                $this->writeToFile($price, $money, $coins, $status);
                $status = [
                    'time' => null,
                    'type' => null
                ];
            }

            // waiting
            $t2 = Carbon::now();
            $wait = floor(abs(30 - $t2->diffInMilliseconds($t1) / 1000));
            sleep($wait);
        }
    }

    private function writeToFile(float $price, float $money, float $coins, array $status): void
    {
        $file = file_get_contents($this->filename . $this->period);
        $file .= $status['time'] . ';' . $status['type'] . ';' . $price . ';' . $money . ';' . $coins . "\n";
        file_put_contents($this->filename . $this->period, $file);
    }

    private function sendStatus(string $statusType, float $money, float $coins): array
    {
        $status = [
            'time' => Carbon::now()->timestamp,
            'type' => $statusType
        ];
        $text = '(' . $this->filename . ')Time: ' . $status['time'] . "\nType: " . $status['type'] . "\nMoney: " . $money . "\nCoins: " . $coins . ' ' . $this->symbol;
        TelegramService::sendMessage($this->period, $text);

        return $status;
    }

    /**
     * Check Liquidation status
     *
     * @param array $buyPrices
     * @param int $buyCount
     * @param float $price
     * @return void
     */
    public function checkLiquidation(array $buyPrices, int $buyCount, float $price): void
    {
        if ($buyCount != 0) {
            foreach ($buyPrices as $buyPrice) {
                $diffPercentage = ($price * 100) / $buyPrice;
                switch ($diffPercentage) {
                    case $diffPercentage < 80 || $diffPercentage > 120:
                        $this->sendStatus('Liquidation x5  (' . $diffPercentage . ') period: ' . $this->period, 0, 0);
                        break;
                    case $diffPercentage < 90 || $diffPercentage > 110:
                        $this->sendStatus('Liquidation x10 (' . $diffPercentage . ') period: ' . $this->period, 0, 0);
                        break;
                    case $diffPercentage < 93.34 || $diffPercentage > 106.66:
                        $this->sendStatus('Liquidation x15 (' . $diffPercentage . ') period: ' . $this->period, 0, 0);
                        break;
                    case $diffPercentage < 95 || $diffPercentage > 105:
                        $this->sendStatus('Liquidation x20 (' . $diffPercentage . ') period: ' . $this->period, 0, 0);
                        break;
                    case $diffPercentage < 96 || $diffPercentage > 104:
                        $this->sendStatus('Liquidation x25 (' . $diffPercentage . ') period: ' . $this->period, 0, 0);
                        break;
                }
            }
        }
    }
}
