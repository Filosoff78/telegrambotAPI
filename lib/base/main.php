<?php
namespace PGK\TelegramBot\Base;
use Bitrix\Main\Config\Option;

class Main
{
    static function unSetHook() {
        $config = self::getSettings();

        try {
            $telegram = new \Longman\TelegramBot\Telegram($config['apiKey'], $config['name']);

            $result = $telegram->deleteWebhook();

            echo $result->getDescription();
        } catch (\Longman\TelegramBot\Exception\TelegramException $e) {
            echo $e->getMessage();
        }

    }
    static function setHook() {
        $config = self::getSettings();

        try {
            $telegram = new \Longman\TelegramBot\Telegram($config['apiKey'], $config['name']);

            $result = $telegram->setWebhook($config['webhook']['url']);

            echo $result->getDescription();
        } catch (\Longman\TelegramBot\Exception\TelegramException $e) {
            echo $e->getMessage();
        }
    }

    static function getSettings(): array
    {
        $optionSettings = \Bitrix\Main\Config\Option::getForModule('pgk.telegrambot');

        return [
            'name' => $optionSettings['BOT_NAME'],
            'registrationUrl' => ((!empty($_SERVER['HTTPS'])) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] .
                $optionSettings['REGISTRATION_URL'],
            'logging' => [
                'debug' => __DIR__ . '/logs/php-telegram-bot-debug.log',
                'error' => __DIR__ . '/logs/php-telegram-bot-error.log',
                'update' => __DIR__ . '/logs/php-telegram-bot-update.log',
            ],
            'limiter' => [
                'enabled' => true,
            ],
            'apiKey' =>  $optionSettings['API_KEY'],
            'webhook' => [
                'url' => ((!empty($_SERVER['HTTPS'])) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] .
                    '/local/modules/pgk.telegrambot/lib/base/hook.php',
            ],
        ];
    }
}
