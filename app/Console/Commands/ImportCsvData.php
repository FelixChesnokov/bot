<?php

namespace App\Console\Commands;

use App\Models\Candle;
use Illuminate\Console\Command;

class ImportCsvData extends Command
{
/**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import-csv-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports csv file';

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
        $filePath = 'BTCUSDT_Binance_futures_data_minute.csv';
        $file = new \SplFileObject($filePath);
        $file->setFlags(\SplFileObject::READ_CSV);
        foreach ($file as $key => $row) {
            if($key > 1) {
                Candle::create([
                    'timestamp' => $row[0],
                    'time'      => $row[1],
                    'pair'      => $row[2],
                    'open'      => $row[3],
                    'high'      => $row[4],
                    'low'       => $row[5],
                    'close'     => $row[6],
                ]);
            }
        }
    }
}
