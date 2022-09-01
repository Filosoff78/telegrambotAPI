<?php
namespace PGK\TelegramBot;

use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;
use Bitrix\Main\DB\SqlQueryException;
use Bitrix\Main\Localization\Loc,
    Bitrix\Main\ORM\Data\DataManager,
    Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;

Loc::loadMessages(__FILE__);

class TelegramTable extends DataManager
{
    public static function getTableName()
    {
        return 'pgk_telegram';
    }

    public static function getMap()
    {
        $modulesFields = [];
        $modules = explode(',', Option::get('pgk.telegrambot', 'MODULES'));
        foreach ($modules as $moduleName) {
            $path = "{$_SERVER['DOCUMENT_ROOT']}/local/modules/{$moduleName}/lib/telegram/telegramtable.php";
            if(file_exists($path))
            {
                $init = include_once $path;
                $modulesFields[] = $init;
            }
        }

        return array_merge([
            (new IntegerField('TELEGRAM_ID'))
                ->configurePrimary()
                ->configureTitle('ИД в телеграме'),

            (new IntegerField('BITRIX_ID'))
                ->configureTitle('BITRIX_ID'),
        ], [...$modulesFields]);
    }

    public static function addColumn(string $columnName)
    {
        $tableName = self::getTableName();
        $connection = Application::getConnection();

        try {
            $connection->query("SELECT $columnName FROM $tableName;");
        } catch (SqlQueryException $e) {
            $connection->query("ALTER TABLE $tableName ADD $columnName INT NOT NULL;");
        }
        return true;
    }

    public static function deleteColumn($columnName)
    {
        $tableName = self::getTableName();
        $connection = Application::getConnection();
        $connection->dropColumn($tableName, $columnName);
    }
}
