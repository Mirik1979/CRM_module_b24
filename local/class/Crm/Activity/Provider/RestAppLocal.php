<?php
/**
 * Created by PhpStorm.
 * @author Alexander Danilin <danilin2010@yandex.ru>
 * Date: 09.09.2019
 * Time: 4:07
 */

namespace local\Crm\Activity\Provider;

use Bitrix\Crm\Activity\Provider\RestApp as M;

class RestAppLocal extends M
{
    /**
     * @param array $activity Activity data.
     * @return array Fields.
     */
    public static function getFieldsForEdit(array $activity)
    {
        $parentFields = parent::getFieldsForEdit($activity);
        $fields = array(
            array(
                'LABEL' => 'Контакт',
                'TYPE' => 'COMMUNICATIONS2'
            )
        );
        return array_merge($parentFields,$fields);
    }
}