<?php

namespace App\Services;

class IndicatorsService
{
    const EMA_K_12 = 12;
    const EMA_K_26 = 26;
    const SIGNAL = 9;

    const MA_PERIOD = 5;

    public function ema($closePrices, $period)
    {
        $k = 2/(1 + $period);
        $ema = [];

        foreach ($closePrices as $key => $closePrice) {
            if($key < $period - 1) {
                continue;
            }
            $priceK[$key] = $closePrice * $k;

            if($key == $period - 1) {
                $ema[$key] = array_sum(array_slice($closePrices,0, $period))/($period);
            } elseif (isset($emaYesterday[$key])) {
                $ema[$key] = $priceK[$key] + $emaYesterday[$key];
            }

            $emaYesterday[$key + 1] = $ema[$key] * (1 - $k);
        }

        return $ema;
    }

    public function macdGisto($candles)
    {
        $closePrices = array_column($candles, '4');

        $ema12 = $this->ema($closePrices, self::EMA_K_12);
        $ema26 = $this->ema($closePrices, self::EMA_K_26);

        $kSignal = 2/(1+self::SIGNAL);

        foreach ($ema26 as $key => $ema26Item) {
            $macd[$key] = $ema12[$key] - $ema26[$key];
            $macdK[$key] = $macd[$key] * $kSignal;
        }

        $signals = [];
        foreach ($macd as $key => $item) {
            if($key < self::EMA_K_26 + self::SIGNAL - 1) {
                continue;
            }

            if($key == self::EMA_K_26 + self::SIGNAL - 1) {
                $signals[$key] = array_sum(array_slice($macd,1, self::SIGNAL))/self::SIGNAL;
            } elseif (isset($macdK[$key]) && isset($siglanlsYesterday[$key])) {
                $signals[$key] = $macdK[$key] + $siglanlsYesterday[$key];
            }

            $siglanlsYesterday[$key + 1] = $signals[$key] * (1 - $kSignal);
        }

        $macdGisto = [];
        foreach ($signals as $key => $signal) {
            $macdGisto[$key] = $macd[$key] - $signals[$key];
        }

        return $macdGisto;
    }


    public function ma($candles)
    {
        $closePrices = array_column($candles, '4');
        $ma = [];

        foreach ($closePrices as $key => $closePrice) {
            $ma[self::MA_PERIOD + $key] = array_sum(array_slice($closePrices, $key, self::MA_PERIOD + $key))/self::MA_PERIOD;
        }

        return $ma;
    }
}
