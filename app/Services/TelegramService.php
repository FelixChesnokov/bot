<?php

namespace App\Services;

use GuzzleHttp\Client;
use Telegram\Bot\Api;

class TelegramService
{
//    const CHAT_IDS = [193252185, 418217562, 687276454];
    const CHAT_IDS = [193252185];

    private $telegramService;

    public function __construct()
    {
        $this->telegramService = new Api();
    }

    /**
     * Send message to Telegram
     *
     * @param string $text
     * @return void
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function sendMessage(string $text) : void
    {
        foreach (self::CHAT_IDS as $chatId) {
            try {
                $this->telegramService->sendMessage([
                    'chat_id' => $chatId,
                    'text' => $text
                ]);
            } catch (\Exception $e) {
                \Log::info('TelegramService', [$e]);
            }
        }
    }
}
