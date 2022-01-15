<?php

return [
    'commands' => [
        Telegram\Bot\Commands\HelpCommand::class,
        \App\Console\Commands\Telegram\StopTradingBotCommand::class
    ],
];
