<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
use PGK\TelegramBot\Helper;
class TelegramBot extends \CBitrixComponent
{
    public function onPrepareComponentParams($params)
    {
        return $params;
    }

    public function executeComponent()
    {
        \Bitrix\Main\Loader::includeModule('pgk.telegrambot');
        $this->arParams['bot'] = \PGK\TelegramBot\Base\Main::getSettings();

        $this->createHash();
        $this->includeComponentTemplate();
    }

    public function createHash(): bool
    {
        global $USER;
        $userId = $USER->GetID();
        $this->arParams['hash'] = Helper::getToken();

        (new \PGK\TelegramBot\Cache())->set($this->arParams['hash'], $userId);

        $this->arParams['bot']['url'] = "https://t.me/".$this->arParams['bot']['name']."?start=".$this->arParams['hash'];
        return true;
    }
}
