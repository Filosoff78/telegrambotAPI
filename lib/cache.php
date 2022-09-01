<?php
namespace PGK\TelegramBot;

class Cache extends \Memcache{
    private \Memcache $memcache;
    private $CACHE_TIME = 600;

    public function __construct()
    {
        $this->memcache = new \Memcache;
        $this->memcache->connect(BX_SECURITY_SESSION_MEMCACHE_HOST, BX_SECURITY_SESSION_MEMCACHE_PORT);
    }

    public function __destruct()
    {
        $this->memcache->close();
    }

    public function set($key, $var, $flag = null, $expire = null): bool
    {
        return $this->memcache->set($key, $var);
    }

    public function get($key, &$flags = null)
    {
        return $this->memcache->get($key);
    }

    public function createUser ($telegramId)
    {
        $userId = TelegramTable::query()
            ->where('TELEGRAM_ID', $telegramId)
            ->setSelect(['BITRIX_ID'])
            ->fetch()['BITRIX_ID'];

        $tmpObject = new \stdClass;
        $tmpObject->user = [
            'telegram' => $telegramId,
            'id' => $userId ?? null,
        ];
        return $this->memcache->set($telegramId, $tmpObject, false, $this->CACHE_TIME);
    }

    public function getInfo ($key)
    {
        if(!\PGK\TelegramBot\User::checkRegistration($key)) return false;
        $info = $this->memcache->get($key);
        if (!$info) {
            $this::createUser($key);
            return $this->memcache->get($key)->user;
        }
        return $info->user;
    }

    public function updateInfo ($key, $property, $value = null) : bool
    {
        $info = $this->memcache->get($key);
        if(!$info) return false;

        if (is_array($property)) {
            foreach ($property as $k => $v) {
                $info->user[$k] = $v;
            }
        }
        else $info->user[$property] = $value;
        return $this->memcache->replace($key, $info, false, $this->CACHE_TIME);
    }

    public function clear ()
    {
        return $this->memcache->flush();
    }
}
