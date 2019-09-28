<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Tasks\Internals\Counter;
use Bitrix\Tasks\Internals\Counter\Name;
use Bitrix\Tasks\Util\User; ?>
<?
CUtil::InitJSCore(array("popup"));

if (
	!isset($arResult["User"]["ID"])
	|| (
		$USER->IsAuthorized()
		&& $arResult["User"]["ID"] == $USER->GetID()
		&& $arParams["PAGE_ID"] != "user"
	)
)
{
	return;
}


$this->addExternalCss(SITE_TEMPLATE_PATH."/css/profile_menu.css");
$bodyClass = $APPLICATION->GetPageProperty("BodyClass");
$APPLICATION->SetPageProperty("BodyClass", ($bodyClass ? $bodyClass." " : "")."profile-menu-mode");

if (!(isset($_REQUEST["IFRAME"]) && $_REQUEST["IFRAME"] === "Y"))
{
	$this->SetViewTarget("above_pagetitle", 100);
}
elseif($arParams['PAGE_ID'] == 'user')
{
	$this->SetViewTarget("below_pagetitle", 100);
}

$className = '';
if ($arResult["User"]["TYPE"] == 'extranet')
{
	$className = ' profile-menu-user-info-extranet';
}
elseif ($arResult["User"]["TYPE"] == 'email')
{
	$className = ' profile-menu-user-info-email';
}
/*
elseif ($arResult["User"]["TYPE"] == 'imconnector')
{
	$className = ' profile-menu-user-info-imconnector';
}
elseif ($arResult["User"]["TYPE"] == 'bot')
{
	$className = ' profile-menu-user-info-bot';
}
elseif ($arResult["User"]["TYPE"] == 'replica')
{
	$className = ' profile-menu-user-info-replica';
}
*/
elseif ($arResult["User"]["IS_EXTRANET"] == 'Y')
{
	$className = ' profile-menu-user-info-extranet';
}
?>

<?
$items = array(
	array
	(
		"TEXT" => GetMessage("SONET_UM_GENERAL"),
		"CLASS" => "",
		"CLASS_SUBMENU_ITEM" => "",
		"ID" => "profile",
		"SUB_LINK" => "",
		"COUNTER" => "",
		"COUNTER_ID" => "",
		"IS_ACTIVE" => 1,
		"IS_LOCKED" => "",
		"IS_DISABLED" => 1
	),
);

if (
	is_array($arResult["CanView"])
	&& !!$arResult["CanView"]['tasks']
)
{
	$uri = new \Bitrix\Main\Web\Uri($arResult["Urls"]['tasks']);
	$uri->addParams(array("IFRAME" => "Y"));
	$redirect = $uri->getUri();

	$items = array_merge($items, array(
		array
		(
			"ID" => "tasks",
			"TEXT" => $arResult["Title"]['tasks'],
			"ON_CLICK" => "BX.SidePanel.Instance.open('".$uri->getUri()."', { width: 1000 })",
			'SUB_LINK' => array(
				'CLASS' => '',
				'URL' => SITE_DIR."company/personal/user/".$arResult["User"]["ID"]."/tasks/task/edit/0/"
			),
		)
	));
}

if (
	is_array($arResult["CanView"])
	&& !!$arResult["CanView"]['calendar']
)
{
	$uri = new \Bitrix\Main\Web\Uri($arResult["Urls"]['calendar']);
	$uri->addParams(array("IFRAME" => "Y"));
	$redirect = $uri->getUri();

	$items = array_merge($items, array(
		array
		(
			"ID" => "calendar",
			"TEXT" => $arResult["Title"]['calendar'],
			"ON_CLICK" => "BX.SidePanel.Instance.open('".$uri->getUri()."', { width: 1000 })"
		)
	));
}

if (
	is_array($arResult["CanView"])
	&& !!$arResult["CanView"]['files']
)
{
	$uri = new \Bitrix\Main\Web\Uri($arResult["Urls"]['files']);
	$uri->addParams(array("IFRAME" => "Y"));
	$redirect = $uri->getUri();

	$items = array_merge($items, array(
		array
		(
			"ID" => "files",
			"TEXT" => $arResult["Title"]['files'],
			"ON_CLICK" => "BX.SidePanel.Instance.open('".$uri->getUri()."', { width: 1000 })"
		)
	));
}

if (
	is_array($arResult["CanView"])
	&& !!$arResult["CanView"]['blog']
)
{
	$uri = new \Bitrix\Main\Web\Uri($arResult["Urls"]['blog']);
	$uri->addParams(array("IFRAME" => "Y"));
	$redirect = $uri->getUri();

	$items = array_merge($items, array(
		array
		(
			"ID" => "blog",
			"TEXT" => $arResult["Title"]['blog'],
			"ON_CLICK" => "BX.SidePanel.Instance.open('".$uri->getUri()."', {
				loader: '".$this->getFolder()."/images/slider/livefeed.svg', 
				width: 1000 
			})"
		)
	));
}

if (
	is_array($arResult["CanView"])
	&& !!$arResult["CanView"]['tasks']
	&& checkEffectiveRights($arResult["User"]["ID"])
)
{
	$uri = new \Bitrix\Main\Web\Uri($arResult["Urls"]['tasks']);
	$uri->addParams(array("IFRAME" => "Y"));
	$redirect = $uri->getUri();
	\CModule::includeModule('tasks');

	$efficiencyUrl = (
		$arResult['isExtranetSite']
			? SITE_DIR."contacts/personal/user/".$arResult["User"]["ID"]."/tasks/effective/"
			: SITE_DIR."company/personal/user/".$arResult["User"]["ID"]."/tasks/effective/"
	);
	$items[] = array(
		"TEXT" => GetMessage("SONET_UM_EFFICIENCY"),
		"ON_CLICK" => "BX.SidePanel.Instance.open('".$efficiencyUrl."', { width: 1000 })",
		"COUNTER" => Counter::getInstance($arResult["User"]["ID"])->get(Name::EFFECTIVE),
		'MAX_COUNTER_SIZE'=>100,
		'COUNTER_ID' => 'effective_counter',
		'ID' => 'effective_counter',
		'CLASS' => 'effective_counter',
	);
}

function checkEffectiveRights($viewedUser)
{
	//TODO move to tasks/security later
	\Bitrix\Main\Loader::includeModule('tasks');
	$currentUser = User::getId();

	if (!$viewedUser)
	{
		return false;
	}

	return
		$currentUser == $viewedUser ||
		User::isSuper($currentUser) ||
		User::isBossRecursively($currentUser, $viewedUser);
}

if (
	is_array($arResult["CurrentUserPerms"])
	&& is_array($arResult["CurrentUserPerms"]["Operations"])
	&& !!$arResult["CurrentUserPerms"]["Operations"]["timeman"]
)
{
	$items = array_merge($items, array(
		array
		(
			"ID" => "timeman",
			"TEXT"     => GetMessage("SONET_UM_TIME"),
			"ON_CLICK" => "BX.SidePanel.Instance.open('".SITE_DIR."timeman/timeman.php?&USERS=U".$arResult["User"]["ID"]."&apply_filter=Y', { width: 1000 })"
		),
		array
		(
			"ID" => "work_report",
			"TEXT"     => GetMessage("SONET_UM_REPORTS"),
			"ON_CLICK" => "BX.SidePanel.Instance.open('".SITE_DIR."timeman/work_report.php', { width: 1000 })"
		)
	));
}

if (
	is_array($arResult["CurrentUserPerms"])
	&& is_array($arResult["CurrentUserPerms"]["Operations"])
	&& !!$arResult["CurrentUserPerms"]["Operations"]['viewgroups']
)
{
	$uri = new \Bitrix\Main\Web\Uri($arResult["Urls"]['groups']);
	$uri->addParams(array("IFRAME" => "Y"));
	$redirect = $uri->getUri();

	$items = array_merge($items, array(
		array
		(
			"ID" => "groups",
			"TEXT" => GetMessage("SONET_UM_GROUPS"),
			"ON_CLICK" => "BX.SidePanel.Instance.open('".$arResult["Urls"]['groups']."', { width: 1000 })"
		)
	));
}

$APPLICATION->IncludeComponent(
	"bitrix:main.interface.buttons",
	"",
	array(
		"ID" => "socialnetwork_profile_menu_user_".$arResult["User"]["ID"],
		"ITEMS" => $items,
		"DISABLE_SETTINGS" => !(
			$USER->isAuthorized()
			&& (
				$USER->getId() == $arResult["User"]["ID"]
				|| (\Bitrix\Main\Loader::includeModule('socialnetwork') && \CSocNetUser::isCurrentUserModuleAdmin())
			)
		)
	)
);
?>
<?$this->EndViewTarget();?>