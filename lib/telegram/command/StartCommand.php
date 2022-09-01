<?php

/**
 * This file is part of the TelegramBot package.
 *
 * (c) Avtandil Kikabidze aka LONGMAN <akalongman@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Longman\TelegramBot\Commands\UserCommands;

use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Request;
use \PGK\TelegramBot\User;

/**
 * Start command
 */
class StartCommand extends UserCommand
{
    /**
     * @var string
     */
    protected $name = 'start';

    /**
     * @var string
     */
    protected $description = 'Регистрация пользователя';

    /**
     * @var string
     */
    protected $usage = '/start <hash>';


    /**
     * @var string
     */
    protected $version = '1.2.0';

    /**
     * Command execute method
     *
     * @return ServerResponse
     */
    public function execute(): ServerResponse
    {
        $message = $this->getMessage();
        $text    = $message->getText(true);
        $user_id = $message->getFrom()->getId();


        if (!User::checkRegistration($user_id, false)) {
            if ($text === '') {
                return $this->replyToChat('Не указан Ваш hash: ' . $this->getUsage());
            } else {
                return User::registration($user_id, $text);
            }
        }
        else return $this->replyToChat('Мы с вам уже знакомы.');
    }
}
