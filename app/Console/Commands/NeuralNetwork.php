<?php

namespace App\Console\Commands;

use App\Models\Candle;
use Phpml\Classification\SVC;
use Phpml\SupportVectorMachine\Kernel;
use Illuminate\Console\Command;
use Phpml\Classification\KNearestNeighbors;
use Phpml\Regression\LeastSquares;
use Phpml\Regression\SVR;

class NeuralNetwork extends Command
{
/**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'neural-network';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Neural Network';

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
        // test open price

//        $samples = [
//            [32762.2,  32782.74,  32783.5, 32779.99],// 32769.53],
//            [32712.72, 32743.72, 32794.33, 32782.94],// 32780.7],
//            [32788.63, 32800.12, 32805.72,    32803],// 32803.97],
//            [32792.38, 32795.37, 32765.26, 32776.13],// 32775.58],
//            [32768.87, 32748.59, 32758.08,    32725],// 32738.63]
//        ];
//        $targets = [32722.66, 32776.75, 32808.91, 32778.7];//, 32746.49];
//
////        $regression = new LeastSquares();
////        $regression = new SVR(Kernel::LINEAR);
//        $classifier = new SVC(
//            Kernel::LINEAR, // $kernel
//            1.0,            // $cost
//            2,              // $degree
//            null,           // $gamma
//            0.0,            // $coef0
//            0.001,          // $tolerance
//            100,            // $cacheSize
//            true,           // $shrinking
//            true            // $probabilityEstimates, set to true
//        );
//        $classifier->train($samples, $targets);
//        $t = $classifier->predictProbability([32735.58, 32713.94, 32767.78, 32772.74]);//, 32770.04]);
//
//
//        // 32757.62
//
//
//        // 2 - float(32735.672835194)
//        // 3 - float(32762.344208673)
//        // 4 - float(33088.802151869)
//
//
//        // 5 - "32830.4"
//
//        die(var_dump($t));



        $samples = [
            $this->getCandles(1,100),
            $this->getCandles(2,101),
            $this->getCandles(3,102),
            $this->getCandles(4,103),
            $this->getCandles(5,104),
            $this->getCandles(6,105),
            $this->getCandles(7,106),
            $this->getCandles(8,107),
            $this->getCandles(9,108),
            $this->getCandles(10,109),
            $this->getCandles(11,110),
        ];
        $targets = [
            $this->getCandles(101,101)[0],
            $this->getCandles(102,102)[0],
            $this->getCandles(103,103)[0],
            $this->getCandles(104,104)[0],
            $this->getCandles(105,105)[0],
            $this->getCandles(106,106)[0],
            $this->getCandles(107,107)[0],
            $this->getCandles(108,108)[0],
            $this->getCandles(109,109)[0],
            $this->getCandles(110,110)[0],
            $this->getCandles(111,111)[0],
        ];

        $regression = new SVR(Kernel::LINEAR);
        $regression->train($samples, $targets);
        $t = $regression->predict($this->getCandles(125,224));

        $need = $this->getCandles(225,225);
        var_dump($need);
        var_dump($t);
        die(var_dump(    ($need[0]*100)/$t   )); // 32776.13
    }

    private function getCandles($from, $to)
    {
        return Candle::whereBetween('id', [$from, $to])->get()->pluck('open')->toArray();
    }
}
