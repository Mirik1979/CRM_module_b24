<?php

namespace local\Crm\Activity\Provider;

use Bitrix\Crm\Activity\Provider\Meeting as M;

class MeetingLocal extends M
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
            ),
            array(
                'LABEL' => 'Объект',
                'TYPE' => 'COMMUNICATIONS3'
            )
        );
        return array_merge($parentFields,$fields);
    }
}