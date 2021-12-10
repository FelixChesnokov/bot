<?php

namespace App\Console\Commands;

use App\Models\Candle;
use Phpml\Classification\NaiveBayes;
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
//        $allCandles = Candle::select('id', 'open')->get()->pluck('open')->skip(50)->take(110)->toArray();
        $allCandles = $this->macd();

        $educateLength = 100;
        $samples = [];
        $targets = [];
        $step = 0;

        // data for education
        while ($step < count($allCandles) - $educateLength) {
            $samples[] = array_slice($allCandles, $step, $educateLength);
            $targets[] = array_slice($allCandles, $step + $educateLength, 1)[0];
            $step++;
        }

//        $samples = [
//            $this->getCandles(1,100),
//            $this->getCandles(2,101),
//            $this->getCandles(3,102),
//            $this->getCandles(4,103),
//            $this->getCandles(5,104),
//            $this->getCandles(6,105),
//            $this->getCandles(7,106),
//            $this->getCandles(8,107),
//            $this->getCandles(9,108),
//            $this->getCandles(10,109),
//            $this->getCandles(11,110),
//        ];
//        $targets = [
//            $this->getCandles(101,101)[0],
//            $this->getCandles(102,102)[0],
//            $this->getCandles(103,103)[0],
//            $this->getCandles(104,104)[0],
//            $this->getCandles(105,105)[0],
//            $this->getCandles(106,106)[0],
//            $this->getCandles(107,107)[0],
//            $this->getCandles(108,108)[0],
//            $this->getCandles(109,109)[0],
//            $this->getCandles(110,110)[0],
//            $this->getCandles(111,111)[0],
//        ];

        $regression = new SVR(Kernel::POLYNOMIAL);
        $regression->train($samples, $targets);

//            '-43.2762730134206',
//            '-60.9155091521917',
//            '-74.9162871241294',
//            '-75.4399820730336',
//            '-55.2185538373665',
//            '-37.7126707180982'

        $lastToLear = last(array_slice($allCandles, 5, 100));
        $need = array_slice($allCandles, 105, 1)[0];
        $t1 = $regression->predict(array_slice($allCandles, 5, 100));

        var_dump('lastToLearn: ' . $lastToLear . '   need: ' . $need . '   res: ' . $t1);



        $lastToLear = last(array_slice($allCandles, 6, 100));
        $need = array_slice($allCandles, 106, 1)[0];
        $t1 = $regression->predict(array_slice($allCandles, 6, 100));

        var_dump('lastToLearn: ' . $lastToLear . '   need: ' . $need . '   res: ' . $t1);



        $lastToLear = last(array_slice($allCandles, 7, 100));
        $need = array_slice($allCandles, 107, 1)[0];
        $t1 = $regression->predict(array_slice($allCandles, 7, 100));

        var_dump('lastToLearn: ' . $lastToLear . '   need: ' . $need . '   res: ' . $t1);



        $lastToLear = last(array_slice($allCandles, 8, 100));
        $need = array_slice($allCandles, 108, 1)[0];
        $t1 = $regression->predict(array_slice($allCandles, 8, 100));

        var_dump('lastToLearn: ' . $lastToLear . '   need: ' . $need . '   res: ' . $t1);


        $lastToLear = last(array_slice($allCandles, 9, 100));
        $need = array_slice($allCandles, 108, 1)[0];
        $t1 = $regression->predict(array_slice($allCandles, 8, 100));

        var_dump('lastToLearn: ' . $lastToLear . '   need: ' . $need . '   res: ' . $t1);


//        $lastToLear = last(array_slice($allCandles, 10, 100));
//        $need = last($allCandles);
//        $t1 = $regression->predict(array_slice($allCandles, 10, 100));
//
//        var_dump('lastToLearn: ' . $lastToLear . '   need: ' . $need . '   res: ' . $t1);



//        '-59.7098847862975',// last
//            '-54.1821691848246',//
//




//        $t2 = $regression->predict(array_slice($allCandles, 1, 101));
//        var_dump($t2);
////
//        $t3 = $regression->predict(array_slice($allCandles, 2, 102));
//        var_dump($t3);
//
//        $t4 = $regression->predict(array_slice($allCandles, 3, 103));
//        var_dump($t4);
//
//        $t5 = $regression->predict(array_slice($allCandles, 4, 104));
//        var_dump($t5);

//        $t1 = $regression->predict($this->getCandles(51,150));
//        $need1 = $this->getCandles(151,151);
//
//        $t2 = $regression->predict($this->getCandles(52,151));
//        $need2 = $this->getCandles(152,152);
//
//        $t3 = $regression->predict($this->getCandles(53,152));
//        $need3 = $this->getCandles(153,153);
//
//        $t4 = $regression->predict($this->getCandles(54,153));
//        $need4 = $this->getCandles(154,154);
//
//        $t5 = $regression->predict($this->getCandles(55,154));
//        $need5 = $this->getCandles(155,155);
//
//        var_dump('need ' . $need1[0]);
//        var_dump('result ' . $t1);
//        var_dump(    ($need1[0]*100)/$t1   );
//        var_dump('######');
//
//        var_dump('need ' . $need2[0]);
//        var_dump('result ' . $t2);
//        var_dump(    ($need2[0]*100)/$t2   );
//        var_dump('######');
//
//        var_dump('need ' . $need3[0]);
//        var_dump('result ' . $t3);
//        var_dump(    ($need3[0]*100)/$t3   );
//        var_dump('######');
//
//        var_dump('need ' . $need4[0]);
//        var_dump('result ' . $t4);
//        var_dump(    ($need4[0]*100)/$t4   );
//        var_dump('######');
//
//        var_dump('need ' . $need5[0]);
//        var_dump('result ' . $t5);
//        var_dump(    ($need5[0]*100)/$t5   );
//        var_dump('######');
//        die(var_dump(    ($need1[0]*100)/$t1   ));
    }

    private function getCandles($from, $to)
    {
        return Candle::whereBetween('id', [$from, $to])->get()->pluck('open')->toArray();
    }

    public function macd()
    {
        return [
            '16.7472580194874',
            '6.77074809482966',
            '8.58057807550023',
            '11.085739808492',
            '15.1081933300245',
            '15.4776112219797',
            '3.53760684415104',
            '-5.67640010997127',
            '-16.8946284850058',
            '-18.9324283919847',
            '-14.8453288004557',
            '-37.9299912595619',
            '-47.2529154533771',
            '-38.1455759552019',
            '-17.4838677212982',
            '40.5662302121223',
            '103.192986116339',
            '131.631371809858',
            '158.86744171829',
            '164.723012730984',
            '150.521333960533',
            '115.035127753957',
            '83.9482892284266',
            '59.5709552251718',
            '50.2484393882331',
            '35.8794151082226',
            '23.9959482561015',
            '10.6265602233754',
            '6.19091815046204',
            '-2.70783733903482',
            '-27.1039306654907',
            '-37.2015711782424',
            '-46.877099265378',
            '-39.4266668686097',
            '-48.7309561702591',
            '-54.6185818980292',
            '-53.4094635655047',
            '-47.4718110825452',
            '-43.5278846500937',
            '-33.4961567069568',
            '-14.9660314317225',
            '-4.36526817033882',
            '-16.5773752010253',
            '-26.1475300799509',
            '-21.6527324026863',
            '-25.7848650074068',
            '-33.3972070995997',
            '-44.2352051093687',
            '-46.4442942142888',
            '-35.7251023107588',
            '-31.9546945306346',
            '-26.6030681028721',
            '-31.0791093939338',
            '-37.8720522544351',
            '-47.5510080916834',
            '-52.1702410258153',
            '-48.3233397220672',
            '-41.1250275347897',
            '-29.13646613907',
            '-19.0866897317182',
            '-12.3927743829095',
            '-1.68698226997915',
            '0.871117247642502',
            '-0.47223706918075',
            '-10.9611421965289',
            '-16.2307208572498',
            '-11.4075683307867',
            '-8.31702859817688',
            '-3.71454334661905',
            '-0.89630333661745',
            '-4.52408192106037',
            '1.27416314323705',
            '-4.60822380662958',
            '-32.2839570362641',
            '-54.8590735265586',
            '-64.9477253875097',
            '-61.9740764136902',
            '-67.845480705788',
            '-60.6301200262752',
            '-57.8412044941216',
            '-50.9545026788256',
            '-45.474406928965',
            '-11.4417971353734',
            '16.1267058409189',
            '39.1572449447161',
            '41.8142690416515',
            '69.9994470983292',
            '74.5912890787119',
            '67.1645986930502',
            '54.1795558960042',
            '23.1947627065152',
            '6.51407008163415',
            '-11.0373472940988',
            '-37.4479540933154',
            '-59.8519630144162',
            '-66.5982201755699',
            '-68.3270326213514',
            '-60.7672592765869',
            '-59.9971986047679',
            '-59.7098847862975',// last
            '-54.1821691848246',//
            '-40.947661103181',
            '-37.1401116374433',
            '-37.0835984006434',
            '-43.2762730134206',
            '-60.9155091521917',
            '-74.9162871241294',
            '-75.4399820730336',
            '-55.2185538373665',
            '-37.7126707180982'
        ];
    }
}


// https://api.binance.com/api/v3/klines?symbol=BTCUSDT&interval=1m

