<?php

namespace App\Services;

use GuzzleHttp\Client;

class BinanceService
{
    public $candles;

    public $tax = 0.0004;

    const LONG = 'LONG';
    const SHORT = 'SHORT';

    /**
     * Get Candles from Binance
     *
     * @param string $symbol
     * @param string $interval
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getCandles(string $symbol, string $interval) : array
    {
        $client = new Client();
        $res = $client->request(
            'GET',
            'https://api.binance.com/api/v3/klines?symbol=' . $symbol . '&interval=' . $interval
        );
        $this->candles = json_decode($res->getBody());
        return $this->candles;
    }

    /**
     * Get Open prices
     *
     * @return array
     */
    public function getOpenPrices() : array
    {
        return array_column($this->candles, '1');
    }

    /**
     * Get Close prices
     *
     * @return array
     */
    public function getClosePrices() : array
    {
        return array_column($this->candles, '4');
    }

    /**
     * Get candle timestamps
     *
     * @return array
     */
    public function getTimestamps() : array
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
     * @return array
     */
    public function buy(float $money, float $coins, float $price, float $howMuchBuy) : array
    {
        $taxValue = $howMuchBuy * $this->tax;
        if($money >= $howMuchBuy + $taxValue) {
            return [
                'money' =>  $money - $howMuchBuy - $taxValue,
                'coins' => $coins + $howMuchBuy/$price,
                'status' => 'bought'
            ];
        } else {
            return [
                'money' => $money,
                'coins' => $coins,
                'status' => 'can not bought'
            ];
        }
    }

    /**
     * Sell futures with tax
     *
     * @param float $money
     * @param float $coins
     * @param float $price
     * @param int $buyCount
     * @param string $type
     * @return array
     */
    public function sell(float $money, float $coins, float $price, int $buyCount, string $type) : array
    {
//        $taxValue = $coins * $price * $this->tax * $buyCount;
//        return [
//            'money' =>  $money + $coins * $price - $taxValue,
//            'coins' => 0,
//            'buyCount' => 0,
//            'status' => 'sell'
//        ];

        $taxValue = $coins * $price * $this->tax * $buyCount;
        if($type == 'long') {

            return [
                'money' =>  $money + $coins * $price - $taxValue,
                'coins' => 0,
                'buyCount' => 0,
                'status' => '[Long] Close position'
            ];
        } else {

            return [
                'money' =>  $money + $coins * $price - $taxValue,
                'coins' => 0,
                'buyCount' => 0,
                'status' => '[Short] Close position'
            ];
        }
    }
}
