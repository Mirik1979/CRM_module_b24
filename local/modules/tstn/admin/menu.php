<?php

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

$menu = [
    [
        'parent_menu' => 'global_menu_content',
        'sort' => 400,
        'text' => Loc::getMessage('TSTN_MENU_TITLE'),
        'title' => Loc::getMessage('TSTN_MENU_TITLE'),
        'url' => 'tstn_index.php',
        'items_id' => 'menu_references',
        'items' => [
            [
                'text' => Loc::getMessage('TSTN_SUBMENU_TITLE'),
                'url' => 'tstn_index.php?param1=paramval&lang=' . LANGUAGE_ID,
                'more_url' => ['tstn_index.php?param1=paramval&lang=' . LANGUAGE_ID],
                'title' => Loc::getMessage('TSTN_SUBMENU_TITLE'),
            ],
        ],
    ],
];

return $menu;
