<?
use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\ModuleManager;

class pgk_telegrambot extends CModule
{
    protected $tables = [
        PGK\TelegramBot\TelegramTable::class
    ];

    public function __construct()
    {
        $arModuleVersion = include('./version.php');

        if (is_array($arModuleVersion) && array_key_exists('VERSION', $arModuleVersion))
        {
            $this->MODULE_VERSION = $arModuleVersion['VERSION'];
            $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
        }

        $this->MODULE_ID = 'pgk.telegrambot';
        $this->MODULE_NAME = 'ПГК: Телеграм бот';
        $this->MODULE_DESCRIPTION = 'Модуль для работы с телеграм ботом';
        $this->MODULE_GROUP_RIGHTS = 'N';
        $this->PARTNER_NAME = 'ПГК';
        $this->PARTNER_URI = 'https://pgk.ru/';
    }

    public function DoInstall()
    {
        ModuleManager::registerModule($this->MODULE_ID);

        self::InstallOptions();

        self::IncludeModule('pgk.telegrambot');
        PGK\TelegramBot\Base\Main::setHook();

        if (!$this->InstallDB() || !$this->InstallFiles()) {
            $this->DoUnInstall();
        }
    }

    public function DoUninstall()
    {
        self::IncludeModule('pgk.telegrambot');
        PGK\TelegramBot\Base\Main::unSetHook();

        if ($this->UnInstallDB() && $this->unInstallFiles()) {
            ModuleManager::unRegisterModule($this->MODULE_ID);
        }
    }

    //region db
    function InstallDB()
    {
        global $APPLICATION;
        Loader::includeModule($this->MODULE_ID);

        try {
            /**
             * @var \Bitrix\Main\ORM\Data\DataManager $table
             */
            foreach ($this->tables as $table) {
                $entity = $table::getEntity();
                $tableName = $entity->getDBTableName();

                if (!Application::getConnection()->isTableExists($tableName)) {
                    $entity->createDbTable();
                }
            }
            return true;
        } catch (Throwable $e) {
            $APPLICATION->ThrowException($e->getMessage());
        }
        return false;
    }

    function UnInstallDB()
    {
        global $APPLICATION;
        Loader::includeModule($this->MODULE_ID);
        try {
            /**
             * @var \Bitrix\Main\ORM\Data\DataManager $table
             */
            foreach ($this->tables as $table) {
                $entity = $table::getEntity();
                $tableName = $entity->getDBTableName();

                if(Application::getConnection()->isTableExists($tableName)) {
                    Application::getConnection()->dropTable($tableName);
                }
            }
            return true;
        } catch (Throwable $e) {
            $APPLICATION->ThrowException($e->getMessage());
        }
        return false;
    }
    //endregion;
    //region files
    function InstallFiles()
    {
        $logsPath = Application::getDocumentRoot().'/local/modules/pgk.telegrambot/lib/base/logs/';
        if(!is_dir($logsPath)) {
            if (mkdir($logsPath)) {
                fopen($logsPath."php-telegram-bot-debug.log", "c+");
                fopen($logsPath."php-telegram-bot-error.log", "c+");
                fopen($logsPath."php-telegram-bot-update.log", "c+");
            }
        }

        return copyDirFiles(
            __DIR__.'/components',
            Application::getDocumentRoot().'/local/components',
            true, true
        );
    }
    function UnInstallFiles()
    {
        $folders = [
            Application::getDocumentRoot().'/local/components/pgk/telegrambot',
        ];

        foreach ($folders as $folder) {
            Bitrix\Main\IO\Directory::deleteDirectory($folder);
        }
        return true;
    }
    //endregion;
    function InstallOptions() {
        Option::set('pgk.telegrambot', 'BOT_NAME', 'PGK_Birthday_Bot');
        Option::set('pgk.telegrambot', 'API_KEY', '5424599252:AAGXn3fsVx549DOW43slB1acakZmV1OKixA');
        Option::set('pgk.telegrambot', 'REGISTRATION_URL', '/local/components/pgk/telegrambot/index.php');
    }
}
