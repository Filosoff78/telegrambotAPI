<?php
namespace PGK\TelegramBot;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;

class Helper {
    /**
     * Подключаем команды у модулей реализующий функционал телеграмбота.
     *
     * @return array
     */
    static function getModulePathCommands() : array
    {
        $moduleNames = array_merge(
            self::getIncludeModules(),
            ['pgk.telegrambot']
        );
        $moduleNamesPath = [];
        foreach ($moduleNames as $moduleName) {
            $path = "{$_SERVER['DOCUMENT_ROOT']}/local/modules/{$moduleName}/lib/telegram/command/";
            if (is_dir($path)) $moduleNamesPath[] = $path;
        }
        return $moduleNamesPath;
    }

    /**
     * Подключаем обработчики запросов у модулей реализующий функционал телеграмбота.
     *
     * @return array
     */
    static function initQuery() : array
    {
        $moduleNames = array_merge(
            self::getIncludeModules(),
            ['pgk.telegrambot']
        );
        $moduleNamesPath = [];
        foreach ($moduleNames as $moduleName) {
            $path = "{$_SERVER['DOCUMENT_ROOT']}/local/modules/{$moduleName}/lib/telegram/query/init.php";
            if(file_exists($path))
            {
                $init = include_once $path;
                $init();
            }
        }
        return $moduleNamesPath;
    }


    /**
     * Получает список модулей реализующий функционал телеграмбота
     *
     * @param bool $include Подключить модули?
     * @return false|array
     */
    static function getIncludeModules($include = false)
    {
        $modules = explode(',', Option::get('pgk.telegrambot', 'MODULES'));
        if (!$include) return $modules;
        else {
            foreach ($modules as $module) {
                if ($module) Loader::includeModule($module);
            }
            return $modules;
        }
    }
    /**
     * Получает пути всех файлов и папок в указанной папке.
     *
     * @param string $dir          Путь до папки (на конце со слэшем или без).
     * @param bool $recursive      Включить вложенные папки или нет?
     * @param bool $includeFolders Включить ли в список пути на папки?
     * @param bool $filterNameFile Исользовать ли фильтр для имени php файла?
     *
     * @return array Вернет массив путей до файлов/папок.
     */
    static function getDirFiles(
        string $dir,
        bool $recursive = true,
        bool $includeFolders = false,
        string $filterNameFile = null): array
    {
        if(!is_dir($dir)) return [];

        $files = [];

        $dir = rtrim($dir, '/\\' );

        foreach(glob( "$dir/{,.}[!.,!..]*", GLOB_BRACE) as $file) {
            if ($filterNameFile && !preg_match('/'.$filterNameFile.'\.php$/', $file)) continue;
            if (is_dir($file)) {
                if ($includeFolders) $files[] = $file;
                if ($recursive) {
                    $files = array_merge(
                        $files,
                        call_user_func( __FUNCTION__, $file, $recursive, $includeFolders));
                }
            } else $files[] = $file;
        }
        return $files;
    }

    static function cryptoRandSecure($min, $max) {
        $range = $max - $min;
        if ($range < 0) return $min;
        $log = log($range, 2);
        $bytes = (int) ($log / 8) + 1;
        $bits = (int) $log + 1; //
        $filter = (1 << $bits) - 1;
        do {
            $rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));
            $rnd = $rnd & $filter;
        } while ($rnd >= $range);
        return $min + $rnd;
    }

    static function getToken ($length=32) {
        $token = "";
        $codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $codeAlphabet.= "abcdefghijklmnopqrstuvwxyz";
        $codeAlphabet.= "0123456789";
        for ($i=0;$i<$length;$i++) {
            $token .= $codeAlphabet[self::cryptoRandSecure(0,strlen($codeAlphabet))];
        }
        return $token;
    }
}
