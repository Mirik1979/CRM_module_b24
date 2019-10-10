<?php
if ($USER->isAdmin()==false) {
    unset($arResult['BUTTONS'][0]['ITEMS'][1]);
    unset($arResult['BUTTONS'][9]);
}