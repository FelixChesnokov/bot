<?php

namespace App\Services;

use GuzzleHttp\Client;

class TelegramService
{
    const CHAT_IDS = [193252185, 418217562];

    const API_KEYS = [
        '1m' => '5034257040:AAEwYccKyu8gFXbDKNH6GSxO-yo5-U2FexU',
        '3m' => '5041697148:AAGq3SGfVODGOOwc-_togWLE2ssTfGcF8EI',
        '5m' => '5085332887:AAGbMU3vQi_tRQd8-BTfycMLNpuc90ozpZI',
        '15m' => '5085154427:AAHPXA7HjCk8b29npaYU698y7aSAzYcR5ls',
        '30m' => '5018089289:AAEqJIBn90ZOVWSpSBQ4Vz11CsdB4cdXCJY',
        '1h' => '5000565798:AAGKz8zdoCl88ICpph5Rnrts8T4yEZZSh9U',
        '2h' => '5005852061:AAGITNRyCy9ESK6L3daX2L621bmzyzbiW0A',
        '4h' => '5018576927:AAEOoW5ESMyMprMqwjpBiYhQdogYVIJZK-M',
        '8h' => '5043710808:AAHrlKzz-6PxG85S9Sl0iidFCmZ44ZbpXMY',
        '1d' => '5046233951:AAEdyIM2M5SPpF4FcrdpZznRI7nS94xtjZM',
    ];

    /**
     * Send message to Telegram
     *
     * @param string $period
     * @param string $text
     * @return void
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function sendMessage(string $period, string $text) : void
    {
        $apiURL = 'https://api.telegram.org/bot' . self::API_KEYS[$period] . '/';
        $client = new Client(array('base_uri' => $apiURL));

        foreach (self::CHAT_IDS as $chatId) {
            try {
                $client->post('sendMessage', ['query' => ['chat_id' => $chatId, 'text' => $text]]);
            } catch (\Exception $e) {
                \Log::info('TelegramService', [$e]);
            }
        }
    }
}
