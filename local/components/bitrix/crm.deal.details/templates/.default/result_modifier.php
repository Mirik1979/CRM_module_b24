<?php
\Bitrix\Main\Diag\Debug::writeToFile("dls");
\Bitrix\Main\Diag\Debug::writeToFile($arResult);

$cit = $arResult['ENTITY_DATA']['LAST_CONTACT_INFOS'][0];

\Bitrix\Main\Diag\Debug::writeToFile("cit");
\Bitrix\Main\Diag\Debug::writeToFile($cit);
//\Bitrix\Main\Diag\Debug::writeToFile($cit[0]);


$emp = array();
$i = 0;
/*

if (\Bitrix\Main\Loader::includeModule('crm'))
{
    $res = CCrmContact::GetList(array("ID" => ASC), array("UF_CRM_1566294330350" => false));

    while($ob = $res->GetNext())
    {
        \Bitrix\Main\Diag\Debug::writeToFile("arr");
        \Bitrix\Main\Diag\Debug::writeToFile($ob);
        $uri = new \Bitrix\Main\Web\Uri("/crm/contact/details/".$ob['ID']."/");
        $uri->scheme = "http";
        $uri->host = "";
        $uri->port = 80;
        $uri->user = "";
        $uri->pass = "";
        $uri->path = "/crm/contact/details/".$ob['ID']."/";
        $uri->query = "";
        $uri->fragment = "";

        $emp[$i] = array(
            "id" => $ob['ID'],
            "type" => 'CONTACT',
            "title" => $ob['FULL_NAME'],
            "module" => 'crm',
            "subTitle" => $ob['COMPANY_TITLE'],
            "actions" => array(),
            "links" => array(
                //"show" => $uri
            ),
            "attributes" => Array
            (
                "email" => Array
                (
                ),
                "phone" => Array
                (
                )
            )
        );
        $i++;
    }
} */


\Bitrix\Main\Diag\Debug::writeToFile("newres");
\Bitrix\Main\Diag\Debug::writeToFile($emp);

$arResult['ENTITY_DATA']['LAST_CONTACT_INFOS'] = $emp;

$arResult['ENTITY_DATA']['LAST_CONTACT_INFOS'][0] = $cit;

//\Bitrix\Main\Diag\Debug::writeToFile("newres");
//\Bitrix\Main\Diag\Debug::writeToFile($arResult);


// unset($cit[0]);