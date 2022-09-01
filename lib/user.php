<?php
namespace PGK\TelegramBot;

use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Request;

class User
{
    public static function checkRegistration ($telegramId, $sendMessage = true) : bool
    {
        if(TelegramTable::query()
            ->where('TELEGRAM_ID', $telegramId)
            ->queryCountTotal()) {
            return true;
        } else {
            if ($sendMessage) {
                $inline_keyboard = new InlineKeyboard([
                    ['text' => 'Портал ПГК', 'url' => \PGK\TelegramBot\Base\Main::getSettings()['registrationUrl']],
                ]);
                self::sendMessage($telegramId, 'Мы с Вами еще не знакомы, пожалуйста перейдите по ссылке:',
                    ['reply_markup' => $inline_keyboard]);
                return false;
            }
            else return false;
        }
    }

    public static function registration ($telegramId, $hash)
    {
        $cache = new Cache();
        $userId = $cache->get($hash);
        if ($userId) {
            TelegramTable::add([
                'TELEGRAM_ID' => $telegramId,
                'BITRIX_ID' => $userId
            ]);
            $cache->createUser($telegramId);
            return self::sendMessage($telegramId, 'Приятно познакомиться! Теперь вам доступны из вкладки меню.');
        } else return self::sendMessage($telegramId,'Я не смог найти Ваш hash');
    }

    public static function sendMessage($telegramId, string $text = '', array $data = []) {
        return Request::sendMessage(array_merge([
            'chat_id' => $telegramId,
            'text'    => $text,
        ], $data));
    }
}
