<?php

namespace App\Services;

use Phpml\Regression\SVR;
use Phpml\SupportVectorMachine\Kernel;

class PredictService
{
    public function predictMacd($array, $predictCount)
    {
        $i = 0;

        while ($i < $predictCount) {
            $educateLength = count($array) - 10;
            $samples = [];
            $targets = [];
            $step = 0;

            // data for education
            while ($step < count($array) - $educateLength) {
                $samples[] = array_slice($array, $step, $educateLength);
                $targets[] = array_slice($array, $step + $educateLength, 1)[0];
                $step++;
            }

            $regression = new SVR(Kernel::POLYNOMIAL);
            $regression->train($samples, $targets);

            $predictedValue = $regression->predict(array_slice($array, 10, $educateLength));

            array_push($array, $predictedValue);
            $i++;
        }

        return array_slice($array, -($predictCount+1));
    }
}
