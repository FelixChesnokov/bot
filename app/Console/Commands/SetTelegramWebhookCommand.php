<?php

namespace App\Console\Commands;

use App\Services\BinanceService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Lin\Binance\Binance;
use Lin\Binance\BinanceFuture;
use Telegram\Bot\Api;

class SetTelegramWebhookCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:set-webhook';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set Telegram webhook';

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
        $telegramService = new Api();

        $telegramService->setWebhook([
            'url' => env('APP_URL') . '/' . $telegramService->getAccessToken()
        ]);

        return $telegramService->getWebhookInfo();
    }
}
