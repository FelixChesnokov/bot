<?php

namespace App\Console\Commands\SaveBasicBot;

use App\Services\BinanceService;
use App\Services\TelegramService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use LupeCode\phpTraderNative\Trader;

class TradingCommand1 extends Command
{
    private $period = '1m';
    private $symbol = 'BTCUSDT';
    private $filename = 'trading_save_basic_1_command_results';

    private $rsiPeriod = 6;
    private $rsiTop = 80;
    private $rsiBottom = 20;

    private $bbandsPeriod = 21;

    private $buyValue = 0.2;
    private $money = 100;


    protected $signature = 'trading_save_basic_1';

    protected $description = 'Start trading';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        \Log::info('trading_save_basic_1');

        $status = [
            'time' => null,
            'type' => null
        ];
        $money = $this->money;
        $buyCount = 0;
        $coins = 0;
        $buyPrices = [];
        $lastBuyTime = null;

        while (true) {
            $t1 = Carbon::now();

            // get candles
            $binanceService = new BinanceService();
            $binanceService->getCandles($this->symbol, $this->period);

            // get open close prices
            $openPrices = $binanceService->getOpenPrices();
            $closePrices = $binanceService->getClosePrices();



            // get last open close prices
            $penultimateCandleOpen = $binanceService->getPenultimate($openPrices, -3);
            $penultimateCandleClose = $binanceService->getPenultimate($closePrices, -3);

            // get last open close prices
            $lastCandleOpen = $binanceService->getPenultimate($openPrices);
            $lastCandleClose = $binanceService->getPenultimate($closePrices);




            // build indicators
            $rsi = Trader::rsi($closePrices, $this->rsiPeriod);
            $bbands = Trader::bbands($closePrices, $this->bbandsPeriod);

            // get penultimate value of indicators
            $penultimateBbands = [
                'UpperBand' => $binanceService->getPenultimate($bbands['UpperBand'], -3),
                'LowerBand' => $binanceService->getPenultimate($bbands['LowerBand'], -3),
            ];

            $lastRsi = $binanceService->getPenultimate($rsi);
            $lastBbands = [
                'UpperBand' => $binanceService->getPenultimate($bbands['UpperBand']),
                'LowerBand' => $binanceService->getPenultimate($bbands['LowerBand']),
            ];



            // get levels of last closed candles
            $penultimateCandleTopLine = max($penultimateCandleOpen, $penultimateCandleClose);
            $penultimateCandleBottomLine = min($penultimateCandleOpen, $penultimateCandleClose);

            // get levels of last closed candles
            $candleTopLine = max($lastCandleOpen, $lastCandleClose);
            $candleBottomLine = min($lastCandleOpen, $lastCandleClose);



            // get last price
            $price = end($closePrices);

            // check liquidation
            $this->checkLiquidation($buyPrices, $buyCount, $price);

            // check last buy time
            $candleTimestamps = $binanceService->getTimestamps();
            if($lastBuyTime && $lastBuyTime == $binanceService->getPenultimate($candleTimestamps, -3)) {
                $lastBuyTime = null;
            }



            /**
             * MAIN BOT LOGIC START
             */
            // buy
            $waitForBuy = $penultimateBbands['LowerBand'] >= $penultimateCandleBottomLine && $lastCandleClose <= $lastCandleOpen;

            if($lastRsi <= $this->rsiBottom && $lastBbands['LowerBand'] <= $candleTopLine && $lastCandleClose >= $lastCandleOpen && $waitForBuy) {
                if($lastBuyTime == null) {
                    $buyResult = $binanceService->buy($money, $coins, $price, $this->money*$this->buyValue);
                    $money = $buyResult['money'];
                    $coins = $buyResult['coins'];
                    $buyCount++;
                    $buyPrices[] = $price;
                    $lastBuyTime = end($candleTimestamps);

                    $status = $this->sendStatus($buyResult['status'], $money, $coins);
                }

            }

            //sell
            $waitForSell = $penultimateBbands['UpperBand'] <= $penultimateCandleTopLine && $lastCandleClose >= $lastCandleOpen;

            if($lastRsi >= $this->rsiTop && $lastBbands['UpperBand'] >= $candleBottomLine && $lastCandleClose <= $lastCandleOpen && $waitForSell) {
                if($coins > 0) {
                    $buyResult = $binanceService->sell($money, $coins, $price, $buyCount);
                    $money = $buyResult['money'];
                    $coins = $buyResult['coins'];
                    $buyCount = $buyResult['buyCount'];

                    $status = $this->sendStatus($buyResult['status'], $money, $coins);
                }
            }
            /**
             * MAIN BOT LOGIC END
             */


            // update money
            $this->money = $money;

            // save data to the file
            if($status['time']) {
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

    private function writeToFile(float $price, float $money, float $coins, array $status) : void
    {
        $file = file_get_contents($this->filename . $this->period);
        $file .= $status['time'] . ';' . $status['type'] . ';' . $price . ';' . $money . ';' . $coins . "\n";
        file_put_contents($this->filename . $this->period, $file);
    }

    private function sendStatus(string $statusType, float $money, float $coins) : array
    {
        $status = [
            'time' => Carbon::now()->timestamp,
            'type' => $statusType
        ];
        $text = '('.$this->filename.')Time: ' . $status['time'] . "\nType: " . $status['type'] . "\nMoney: " . $money . "\nCoins: " . $coins . ' ' . $this->symbol;
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
    public function checkLiquidation(array $buyPrices, int $buyCount, float $price) : void
    {
        if($buyCount != 0) {
            foreach ($buyPrices as $buyPrice) {
                $diffPercentage = ($price * 100)/$buyPrice;
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
                    case $diffPercentage < 96.67 || $diffPercentage > 103.33:
                        $this->sendStatus('Liquidation x30 (' . $diffPercentage . ') period: ' . $this->period, 0, 0);
                        break;
                    case $diffPercentage < 98 || $diffPercentage > 102:
                        $this->sendStatus('Liquidation x50 (' . $diffPercentage . ') period: ' . $this->period, 0, 0);
                        break;
                }
            }
        }
    }
}
