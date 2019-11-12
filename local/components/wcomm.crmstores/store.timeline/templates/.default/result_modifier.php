<?php

use Bitrix\Main\Type\DateTime;


//echo "<pre>";
//print_r($arResult);
//echo "</pre>";

$sortarray = array();
$histcopy = $arResult['HISTORY_ITEMS'];


foreach ($arResult['HISTORY_ITEMS'] as $key => $histval) {
    $dateex = $histval['CREATED']->toString();
    //print_r($dateex);
    //echo "<br>";
    $datearr = array(
        'date' => $dateex,
        'id'  => $histval['ID']
    );
    array_push($sortarray, $datearr);
}

usort($sortarray, "sort_date");

//echo "<pre>";
//print_r($sortarray);
//echo "</pre>";

$arResult['HISTORY_ITEMS'] = array();
$i = 0;

//echo "<pre>";
//print_r($histcopy);
//echo "</pre>";

foreach ($sortarray as $histitem) {
    $id = $histitem['id'];
    foreach ($histcopy as $val) {
        if($val['ID'] == $id) {
            $arResult['HISTORY_ITEMS'][$i] = $val;
        }
    }
    $i++;
}

//echo "here";
//echo "<pre>";
//print_r($arResult['HISTORY_ITEMS']);


function sort_date($a_new, $b_new) {

    $a_new = strtotime($a_new["date"]);
    $b_new = strtotime($b_new["date"]);

    return $b_new - $a_new;

}

