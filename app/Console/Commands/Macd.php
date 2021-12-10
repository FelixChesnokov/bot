<?php

namespace App\Console\Commands;

use App\Models\Candle;
use GuzzleHttp\Client;
use Illuminate\Console\Command;

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
        $client = new Client();
        $res = $client->request('GET', 'https://api.binance.com/api/v3/klines?symbol=BTCUSDT&interval=15m');

        $candles = json_decode($res->getBody());

        $context = '';
        foreach ($candles as $candle) {
            $context .= $candle[4] . "\n";
        }

        file_put_contents('closePrices', $context);
    }

    private function getCandles($from, $to)
    {
        // https://api.binance.com/api/v3/klines?symbol=BTCUSDT&interval=15m
        return Candle::whereBetween('id', [$from, $to])->get()->pluck('open')->toArray();
    }
}
