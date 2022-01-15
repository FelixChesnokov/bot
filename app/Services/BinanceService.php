<?php

namespace App\Services;

use Lin\Binance\BinanceFuture;

class BinanceService
{
    public $candles;
    public $balance;

    public $tax = 0.0004;

    public $binanceFutures;

    public function __construct()
    {
        $this->binanceFutures = new BinanceFuture(env('BINANCE_API_KEY'), env('BINANCE_SECRET_KEY'));
    }

    /**
     * Get Candles from Binance
     *
     * @param string $symbol
     * @param string $interval
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getCandles(string $symbol, string $interval): array
    {
        try {
            $this->candles = $this->binanceFutures->market()->getKlines(['symbol' => 'BTCUSDT', 'interval' => '5m']);
            return $this->candles;
        } catch (\Exception $e) {
            // send error to Errors telegram bot
            // $error = json_decode($e->getMessage());
        }
    }

    /**
     * Get Balance info
     *
     * @return mixed
     */
    public function getBalance()
    {
        try {
            $this->balance = $this->binanceFutures->user()->getBalance();
            return $this->balance;
        } catch (\Exception $e) {
            // send error to Errors telegram bot
            // $error = json_decode($e->getMessage());
        }
    }

    public function openPosition(string $symbol, string $type, float $coinQuantity)
    {
        try {
            return $this->binanceFutures->trade()->postOrder([
                'symbol' => $symbol,
                'side' => $type,
                'type' => 'MARKET',
                'quantity' => $coinQuantity,
            ]);
        } catch (\Exception $e) {
            // send error to Errors telegram bot
            // $error = json_decode($e->getMessage());
        }
    }

    public function closePosition(string $symbol)
    {
        try {
            $this->binanceFutures->trade()->deleteAllOpenOrders(['symbol' => $symbol]);
        } catch (\Exception $e) {
            // send error to Errors telegram bot
            // $error = json_decode($e->getMessage());
        }
    }

    /**
     * Get Open prices
     *
     * @return array
     */
    public function getOpenPrices(): array
    {
        return array_column($this->candles, '1');
    }

    /**
     * Get Close prices
     *
     * @return array
     */
    public function getClosePrices(): array
    {
        return array_column($this->candles, '4');
    }

    /**
     * Get candle timestamps
     *
     * @return array
     */
    public function getTimestamps(): array
    {
        return array_column($this->candles, '6');
    }

    /**
     * Get penultimate
     *
     * @param array $array
     * @param int $howMuch
     * @return mixed
     */
    public function getPenultimate(array $array, int $howMuch = -2)
    {
        return array_slice($array, $howMuch, 1)[0];
    }

    /**
     * Buy futures with tax
     *
     * @param float $money
     * @param float $coins
     * @param float $price
     * @param float $howMuchBuy
     * @param string $type
     * @return array
     */
    public function buy(float $money, float $coins, float $price, float $howMuchBuy, string $type): array
    {
        $taxValue = $howMuchBuy * $this->tax;
        if ($money >= $howMuchBuy + $taxValue) {
            return [
                'money' => $money - $howMuchBuy - $taxValue,
                'coins' => $coins + $howMuchBuy / $price,
                'coinsByAction' => $howMuchBuy / $price,
                'status' => '[' . $type . '] Bought'
            ];
        } else {
            return [
                'money' => $money,
                'coins' => $coins,
                'coinsByAction' => 0,
                'status' => '[' . $type . '] Can not bought'
            ];
        }
    }

    /**
     * Sell futures with tax
     *
     * @param float $money
     * @param float $coins
     * @param float $price
     * @param array $buyActions
     * @param string $type
     * @return array
     */
    public function sell(float $money, float $coins, float $price, array $buyActions, string $type): array
    {
        $taxValue = $coins * $price * $this->tax * count($buyActions);
        $value = 0;
        $profit = 0;

        foreach ($buyActions as $buyAction) {
            $value += $buyAction['price'] * $buyAction['coins'];
            if ($type == 'Long') {
                $profit += (1 / $buyAction['price'] - 1 / $price) * $buyAction['value'];
            } else {
                $profit += (1 / $buyAction['price'] - 1 / $price) * $buyAction['value'] * (-1);
            }
        }

        return [
            'money' => $money + $value + $profit*$price - $taxValue,
            'coins' => 0,
            'buyCount' => 0,
            'status' => '[' . $type . '] Close position'
        ];
    }
}
