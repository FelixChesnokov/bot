<?php

namespace App\Console\Commands;

use App\Services\BinanceService;
use App\Services\TelegramService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use LupeCode\phpTraderNative\Trader;

class TradingCommand extends Command
{
    // binance settings
    private $period = '5m';
    private $symbol = 'BTCUSDT';

    //report settings
    private $filename = 'trading_command_results';

    // indicators settings
    private $rsiPeriod = 6;
    private $rsiTop = 70;
    private $rsiBottom = 30;
    private $bbandsPeriod = 21;

    // pocket settings
    private $buyValue = 0.1;
    private $buySensitivity = 0.3;
    private $dontTouchMoney = 10;
    private $pocketValue = 100;
    private $money = 100;
    private $coins = 0;

    // bot settings
    private $binanceService;
    private $status = ['time' => null, 'type' => null];
    private $buyActions = [];
    private $lastBuyTime = null;
    private $longPositionOpen = null;
    private $shortPositionOpen = null;

    protected $signature = 'bot:trading';

    protected $description = 'Start trading basic setting';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $money = $this->money;

        while (true) {
            $t1 = Carbon::now();

            // get candles
            $this->binanceService = new BinanceService();
            $this->binanceService->getCandles($this->symbol, $this->period);

            // get last open close prices
            $openPrices = $this->binanceService->getOpenPrices();
            $closePrices = $this->binanceService->getClosePrices();

            // get penultimate open close prices
            $lastCandleOpen = $this->binanceService->getPenultimate($openPrices);
            $lastCandleClose = $this->binanceService->getPenultimate($closePrices);

            // build indicators
            $rsi = Trader::rsi($closePrices, $this->rsiPeriod);
            $bbands = Trader::bbands($closePrices, $this->bbandsPeriod);

            // get penultimate value of indicators
            $lastRsi = $this->binanceService->getPenultimate($rsi);
            $lastBbands = [
                'UpperBand' => $this->binanceService->getPenultimate($bbands['UpperBand']),
                'LowerBand' => $this->binanceService->getPenultimate($bbands['LowerBand']),
            ];

            // get levels of last closed candles
            $candleTopLine = max($lastCandleOpen, $lastCandleClose);
            $candleBottomLine = min($lastCandleOpen, $lastCandleClose);

            // get last price
            $price = end($closePrices);

            // check last buy time
            $candleTimestamps = $this->binanceService->getTimestamps();
            if ($this->lastBuyTime && $this->lastBuyTime == $this->binanceService->getPenultimate($candleTimestamps, -3)) {
                $this->lastBuyTime = null;
            }


            /**
             * MAIN BOT LOGIC START
             */
            //sell LONG
            if ($lastRsi >= $this->rsiTop && $lastBbands['UpperBand'] <= $candleTopLine) {
                if ($this->coins > 0 && $this->longPositionOpen) {
                    $this->sellAction($price, 'Long');
                }
            }
            //sell SHORT
            if ($lastRsi <= $this->rsiBottom && $lastBbands['LowerBand'] >= $candleBottomLine) {
                if ($this->coins > 0 && $this->shortPositionOpen) {
                    $this->sellAction($price, 'Short');
                }
            }

            // buy LONG
            if ($lastRsi <= $this->rsiBottom && $lastBbands['LowerBand'] >= $candleBottomLine) {
                if ($this->lastBuyTime == null && $money > 2) {
                    $this->buyAction($price, 'Long', end($candleTimestamps));
                }
            }
            // buy SHORT
            if ($lastRsi >= $this->rsiTop && $lastBbands['UpperBand'] <= $candleTopLine) {
                if ($this->lastBuyTime == null && $money > 2) {
                    $this->buyAction($price, 'Short', end($candleTimestamps));
                }
            }
            /**
             * MAIN BOT LOGIC END
             */


            // save data to the file
            if ($this->status['time']) {
                $this->writeToFile($price);
                $this->status = [
                    'time' => null,
                    'type' => null
                ];
            }

            // waiting
            $t2 = Carbon::now();
            $wait = floor(abs(5 - $t2->diffInMilliseconds($t1) / 1000));
            sleep($wait);
        }
    }

    private function sellAction(float $price, string $actionType): void
    {
        // sell action
        $buyResult = $this->binanceService->sell($this->money, $this->coins, $price, $this->buyActions, $actionType);

        // update money and coins
        $this->money = $buyResult['money'];
        $this->coins = $buyResult['coins'];

        // update actions
        $this->buyActions = [];
        if ($actionType == 'Long') {
            $this->longPositionOpen = null;
        } else {
            $this->shortPositionOpen = null;
        }

        // send status
        $this->sendStatus($buyResult['status']);
    }

    private function buyAction(float $price, string $actionType, float $lastCandleTimestamp): void
    {
        // choose how many spend for this action
        $currentBuyValue = $this->calcBuyValue($price, $actionType);

        // buy action
        $buyResult = $this->binanceService->buy($this->money, $this->coins, $price, $currentBuyValue, $actionType);

        // update money and coins
        $this->money = $buyResult['money'];
        $this->coins = $buyResult['coins'];

        // update actions
        $this->buyActions[] = [
            'price' => $price,
            'value' => $currentBuyValue,
            'coins' => $buyResult['coinsByAction']
        ];
        $this->lastBuyTime = $lastCandleTimestamp;
        if ($actionType == 'Long') {
            $this->longPositionOpen = true;
        } else {
            $this->shortPositionOpen = true;
        }

        // send status
        $this->sendStatus($buyResult['status']);
    }

    public function sendStatus(string $statusType): void
    {
        $this->status = [
            'time' => Carbon::now()->format('Y-m-d H:i:s'),
            'type' => $statusType
        ];

        $text = 'Time: ' . $this->status['time'] .
            "\nType: " . $this->status['type'] .
            "\nMoney: " . $this->money .
            "\nCoins: " . $this->coins . ' ' . $this->symbol;

        TelegramService::sendMessage($this->period, $text);
    }

    public function writeToFile(float $price): void
    {
        $file = file_get_contents($this->filename . $this->period);
        $file .= $this->status['time'] . ';' . $this->status['type'] . ';' . $price . ';' . $this->money . ';' . $this->coins . "\n";
        file_put_contents($this->filename . $this->period, $file);
    }

    public function calcBuyValue(float $price, string $type) : int
    {
        $lastBuyValue = end($this->buyActions)['price'];
        if($type == 'Long' && $price > $lastBuyValue || $type == 'Short' && $price < $lastBuyValue) {
            $diffInPercentage = abs(100 - $lastBuyValue * 100 / $price);
            $buyPercentage = ceil($diffInPercentage * $this->buySensitivity) + $this->buyValue;
            return min($this->money - $this->dontTouchMoney, $this->pocketValue * $buyPercentage);
        } else {
            return 0;
        }
    }
}
