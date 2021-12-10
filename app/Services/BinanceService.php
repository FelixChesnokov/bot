<?php

namespace App\Services;

use GuzzleHttp\Client;

class BinanceService
{
    public function getCandles($symbol, $interval)
    {
        $client = new Client();
        $res = $client->request('GET', 'https://api.binance.com/api/v3/klines?symbol='.$symbol.'&interval=' . $interval);
        return json_decode($res->getBody());
    }
}
