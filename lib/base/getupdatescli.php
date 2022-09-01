<?php
namespace PGK\TelegramBot\Base;
use PGK\TelegramBot\Helper;

class getUpdatesCli
{
    static function init ()
    {
        require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

        \Bitrix\Main\Loader::includeModule('pgk.telegrambot');

        \PGK\TelegramBot\Helper::getIncludeModules(true);

        $config = \PGK\TelegramBot\Base\Main::getSettings();

        try {
            $telegram = new \Longman\TelegramBot\Telegram($config['apiKey'], $config['name']);

            //Режим работы без использования БД
            $telegram->useGetUpdatesWithoutDatabase();

            \PGK\TelegramBot\Helper::initQuery();

            $telegram->addCommandsPaths(Helper::getModulePathCommands());

            $server_response = $telegram->handleGetUpdates();

            if ($server_response->isOk()) {
                $update_count = count($server_response->getResult());
                echo date('Y-m-d H:i:s') . ' - Processed ' . $update_count . ' updates';
            } else {
                echo date('Y-m-d H:i:s') . ' - Failed to fetch updates' . PHP_EOL;
                echo $server_response->printError();
            }

        } catch (Longman\TelegramBot\Exception\TelegramException $e) {
            Longman\TelegramBot\TelegramLog::error($e);
        }
    }
}
