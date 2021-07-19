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
        $allCandles = Candle::select('id', 'open')->get()->pluck('open')->skip(50)->take(110)->toArray();

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

        $regression = new SVR(Kernel::LINEAR);
        $regression->train($samples, $targets);



        $t1 = $regression->predict($this->getCandles(51,150));
        $need1 = $this->getCandles(151,151);

        $t2 = $regression->predict($this->getCandles(52,151));
        $need2 = $this->getCandles(152,152);

        $t3 = $regression->predict($this->getCandles(53,152));
        $need3 = $this->getCandles(153,153);

        $t4 = $regression->predict($this->getCandles(54,153));
        $need4 = $this->getCandles(154,154);

        $t5 = $regression->predict($this->getCandles(55,154));
        $need5 = $this->getCandles(155,155);

        var_dump('need ' . $need1[0]);
        var_dump('result ' . $t1);
        var_dump(    ($need1[0]*100)/$t1   );
        var_dump('######');

        var_dump('need ' . $need2[0]);
        var_dump('result ' . $t2);
        var_dump(    ($need2[0]*100)/$t2   );
        var_dump('######');

        var_dump('need ' . $need3[0]);
        var_dump('result ' . $t3);
        var_dump(    ($need3[0]*100)/$t3   );
        var_dump('######');

        var_dump('need ' . $need4[0]);
        var_dump('result ' . $t4);
        var_dump(    ($need4[0]*100)/$t4   );
        var_dump('######');

        var_dump('need ' . $need5[0]);
        var_dump('result ' . $t5);
        var_dump(    ($need5[0]*100)/$t5   );
        var_dump('######');
//        die(var_dump(    ($need1[0]*100)/$t1   ));
    }

    private function getCandles($from, $to)
    {
        return Candle::whereBetween('id', [$from, $to])->get()->pluck('open')->toArray();
    }
}
