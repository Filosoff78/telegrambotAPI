<?php
defined('B_PROLOG_INCLUDED') || die;
use \Bitrix\Main\Loader,
    \Bitrix\Main\Config\Option,
    \Bitrix\Main\Localization\Loc,
    \Bitrix\Main\Diag\Debug;
use PGK\TelegramBot\RenderOptions;

global $APPLICATION, $USER;

if (!$USER->IsAdmin()) {
    return;
}

$request = \Bitrix\Main\HttpApplication::getInstance()->getContext()->getRequest();
$myModuleID = $request['mid'];
Loader::includeModule($myModuleID);

//region options

$tabs[] = array(
    'DIV' => 'general',
    'TAB' => 'ПГК: Телеграм Бот',
    'TITLE' => 'Основные настройки'
);

$modules = [];
$rsInstalledModules = CModule::GetList();
while ($ar = $rsInstalledModules->Fetch())
{
    $modules[$ar['ID']] = $ar['ID'];
}

$options['general'] = [
    'Настройки:',
    [
        'API_KEY',
        'API ключ:',
        Option::get($myModuleID, 'TG_BOT_API_KEY'),
        array("text")
    ],
    [
        'BOT_NAME',
        'Имя бота:',
        Option::get($myModuleID, 'TG_BOT_BOT_NAME'),
        array("text")
    ],
    [
        'REGISTRATION_URL',
        'Ссылка на страницу регистрации:',
        Option::get($myModuleID, 'TG_BOT_REGISTRATION_URL'),
        array("text")
    ],
    [
        'MODULES',
        'Модули:',
        null,
        array("multiselectbox", $modules)
    ],
    'Дополнительно:',
    [
        'LOGS',
        'Логи:',
        null,
        array("tgbotlogs")
    ],
];

//endregion options

if (check_bitrix_sessid() && strlen($_POST['save']) > 0) {
    foreach ($options as $option) {
        (new RenderOptions())->__AdmSettingsSaveOptions($myModuleID, $option);
    }
    LocalRedirect($APPLICATION->GetCurPageParam());
}


/*
 * отрисовка формы
 */
$tabControl = new CAdminTabControl('tabControl', $tabs);
$tabControl->Begin();
?>

<form method="POST"
      action="<? echo $APPLICATION->GetCurPage() ?>?mid=<?= htmlspecialcharsbx($mid) ?>&lang=<?= LANGUAGE_ID ?>" id="baseexchange_form">
    <?php
    foreach($options as $option){
        $tabControl->BeginNextTab();
        (new RenderOptions())->__AdmSettingsDrawList($myModuleID, $option);
    }
    $tabControl->Buttons(array('btnApply' => false, 'btnCancel' => false, 'btnSaveAndAdd' => false));
    echo bitrix_sessid_post();
    $tabControl->End();
    ?>
</form>
