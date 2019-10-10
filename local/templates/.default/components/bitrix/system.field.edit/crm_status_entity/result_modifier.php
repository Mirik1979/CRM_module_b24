<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if(!CModule::IncludeModule("crm"))
	return;
global $USER;
$userPermissions = CCrmPerms::GetCurrentUserPermissions();
$arSupportedTypes = array(); // all entity types are defined in settings
$arParams['ENTITY_TYPE'] = array(); // only entity types are allowed for current user
$arSettings = $arParams['arUserField']['SETTINGS'];

$arSupportedTypes[] = 'COMPANY';
if(CCrmCompany::CheckReadPermission(0, $userPermissions))
{
    $arParams['ENTITY_TYPE'][] = CCrmOwnerType::CompanyName;
}

$arResult['PERMISSION_DENIED'] = (empty($arParams['ENTITY_TYPE']) ? true : false);

$arResult['PREFIX'] = 'N';

$arResult['MULTIPLE'] = 'N';

$arResult['VALUE'] = unserialize($arParams['~arUserField']['VALUE']);

$arResult['SELECTED'] = array();

$selectorEntityTypes = array();

$arResult['USE_SYMBOLIC_ID'] = (count($arParams['ENTITY_TYPE']) > 1);

$arResult['LIST_PREFIXES'] = [
	'DEAL' => 'D',
	'CONTACT' => 'C',
	'COMPANY' => 'CO',
	'LEAD' => 'L',
	'ORDER' => 'O'
];
$arResult['SELECTOR_ENTITY_TYPES'] = [
	'DEAL' => 'deals',
	'CONTACT' => 'contacts',
	'COMPANY' => 'companies',
	'LEAD' => 'leads',
	'ORDER' => 'orders'
];

foreach ($arResult['VALUE'] as $key => $value)
{
	if (empty($value))
	{
		continue;
	}

    $arResult['SELECTED'][] = $value['COMPANY'];
}

$arResult['ENTITY_TYPE'] = array();
// last 50 entity

$arResult['ENTITY_TYPE'][] = 'company';

if (method_exists('CCrmCompany', 'GetTopIDs'))
{
    $IDs = CCrmCompany::GetTopIDs(50, 'DESC', $userPermissions);
    if (empty($IDs))
    {
        $obRes = new CDBResult();
        $obRes->InitFromArray(array());
    }
    else
    {
        $obRes = CCrmCompany::GetListEx(
            array('ID' => 'DESC'),
            array('@ID' => $IDs, 'CHECK_PERMISSIONS' => 'N'),
            false,
            false,
            array('ID', 'TITLE', 'COMPANY_TYPE', 'INDUSTRY', 'LOGO')
        );
    }
}
else
{
    $obRes = CCrmCompany::GetListEx(
        array('ID' => 'DESC'),
        array(),
        false,
        array('nTopCount' => 50),
        array('ID', 'TITLE', 'COMPANY_TYPE', 'INDUSTRY',  'LOGO')
    );
}

$arCompanyTypeList = CCrmStatus::GetStatusListEx('COMPANY_TYPE');
$arCompanyIndustryList = CCrmStatus::GetStatusListEx('INDUSTRY');
$elements = array();
while ($arRes = $obRes->Fetch())
{
    $imageUrl = '';
    if (isset($arRes['LOGO']) && $arRes['LOGO'] > 0)
    {
        $arImg = CFile::ResizeImageGet($arRes['LOGO'], array('width' => 25, 'height' => 25), BX_RESIZE_IMAGE_EXACT);
        if(is_array($arImg) && isset($arImg['src']))
        {
            $imageUrl = $arImg['src'];
        }
    }

    $arRes['SID'] = $arResult['PREFIX'] == 'Y'? 'CO_'.$arRes['ID']: $arRes['ID'];

    $arDesc = Array();
    if (isset($arCompanyTypeList[$arRes['COMPANY_TYPE']]))
        $arDesc[] = $arCompanyTypeList[$arRes['COMPANY_TYPE']];
    if (isset($arCompanyIndustryList[$arRes['INDUSTRY']]))
        $arDesc[] = $arCompanyIndustryList[$arRes['INDUSTRY']];


    $elements[$arRes['ID']] = Array(
        'title' => (str_replace(array(';', ','), ' ', $arRes['TITLE'])),
        'desc' => implode(', ', $arDesc),
        'id' => $arRes['SID'],
        'url' => CComponentEngine::MakePathFromTemplate(COption::GetOptionString('crm', 'path_to_company_show'),
            array(
                'company_id' => $arRes['ID']
            )
        ),
        'image' => $imageUrl,
        'type'  => 'company',
        'selected' => 'N'
    );
}

$arResult['SELECTED'] = array_diff($arResult['SELECTED'], array_keys($elements));

if (!empty($arResult['SELECTED']))
{
    $arCompanyTypeList = CCrmStatus::GetStatusListEx('COMPANY_TYPE');
    $arCompanyIndustryList = CCrmStatus::GetStatusListEx('INDUSTRY');
    $arSelect = array('ID', 'TITLE', 'COMPANY_TYPE', 'INDUSTRY',  'LOGO');
    $obRes = CCrmCompany::GetList(array('ID' => 'DESC'), Array('ID' => $arResult['SELECTED']), $arSelect);
    $ar = Array();
    while ($arRes = $obRes->Fetch())
    {
        $imageUrl = '';
        if (isset($arRes['LOGO']) && $arRes['LOGO'] > 0)
        {
            $arImg = CFile::ResizeImageGet($arRes['LOGO'], array('width' => 25, 'height' => 25), BX_RESIZE_IMAGE_EXACT);
            if(is_array($arImg) && isset($arImg['src']))
            {
                $imageUrl = $arImg['src'];
            }
        }

        $arRes['SID'] = $arResult['PREFIX'] == 'Y'? 'CO_'.$arRes['ID']: $arRes['ID'];
        if (isset($arResult['SELECTED'][$arRes['SID']]))
        {
            unset($arResult['SELECTED'][$arRes['SID']]);
            $sSelected = 'Y';
        }
        else
        {
            if(!empty($arParams['usePrefix']) && isset($arResult['SELECTED'][$arRes['ID']]))
            {
                unset($arResult['SELECTED'][$arRes['ID']]);
                $sSelected = 'Y';
            }
            else
            {
                $sSelected = 'N';
            }
        }


        $arDesc = Array();
        if (isset($arCompanyTypeList[$arRes['COMPANY_TYPE']]))
            $arDesc[] = $arCompanyTypeList[$arRes['COMPANY_TYPE']];
        if (isset($arCompanyIndustryList[$arRes['INDUSTRY']]))
            $arDesc[] = $arCompanyIndustryList[$arRes['INDUSTRY']];

        $elements[$arRes['ID']] = Array(
            'title' => (str_replace(array(';', ','), ' ', $arRes['TITLE'])),
            'desc' => implode(', ', $arDesc),
            'id' => $arRes['SID'],
            'url' => CComponentEngine::MakePathFromTemplate(COption::GetOptionString('crm', 'path_to_company_show'),
                array(
                    'company_id' => $arRes['ID']
                )
            ),
            'image' => $imageUrl,
            'type'  => 'company',
            'selected' => 'N'
        );
	}
}
$arResult['ELEMENTS'] = [];
if(empty($arResult['VALUE'])) {
    $arResult['ELEMENTS'][] = [
        'SELECTED_LIST' => [],
        'ELEMENT' => $elements,
        'TYPE_COMPANY' => ''
    ];
}
else {
    foreach ($arResult['VALUE'] as $arValue) {
        $curElements = $elements;
        $curElements[$arValue['COMPANY_ID']]['selected'] = 'Y';
        $arResult['ELEMENTS'][] = [
            'SELECTED_LIST' => ['CO_'.$arValue['COMPANY'] => 'companies'],
            'ELEMENT' => $curElements,
            'TYPE_COMPANY' => $arValue['TYPE_COMPANY']
        ];
    }
}

$arParams['createNewEntity'] = ($arParams['createNewEntity'] && \Bitrix\Crm\Settings\LayoutSettings::getCurrent()->isSliderEnabled());

if(!empty($arParams['createNewEntity']))
{
	if(!empty($arResult['ENTITY_TYPE']))
	{
		if(count($arResult['ENTITY_TYPE']) > 1)
		{
			$arResult['PLURAL_CREATION'] = true;
		}
		else
		{
			$arResult['PLURAL_CREATION'] = false;
			$arResult['CURRENT_ENTITY_TYPE'] = current($arResult['ENTITY_TYPE']);
		}
	}
	
	$arResult['LIST_ENTITY_CREATE_URL'] = array();
	foreach($arResult['ENTITY_TYPE'] as $entityType)
	{

		$arResult['LIST_ENTITY_CREATE_URL'][$entityType] = \CCrmUrlUtil::addUrlParams(
			\CCrmOwnerType::getDetailsUrl(
				CCrmOwnerType::resolveID($entityType),
				0,
				false,
				array('ENABLE_SLIDER' => true)
			),
			array('init_mode' => 'edit')
		);
	}
}

$ar = CCrmStatus::GetStatusList($arParams['arUserField']['SETTINGS']['ENTITY_TYPE']);
foreach ($ar as $key => $name)
{
    $arr[$key] = $name;
}
$arResult['CRM_STATUS'] = $arr;

?>