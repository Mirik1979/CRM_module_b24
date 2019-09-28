<?php

namespace Sprint\Migration;


class CommCompany20190904114959 extends Version
{

    protected $description = "CommCompany";

    public function up()
    {
        $helper = $this->getHelperManager();


        $helper->Hlblock()->saveHlblock(array (
            'NAME' => 'CommCompany',
            'TABLE_NAME' => 'comm_company',
            'LANG' =>
                array (
                ),
        ));

        $helper->UserTypeEntity()->saveUserTypeEntity(array (
            'ENTITY_ID' => 'HLBLOCK_CommCompany',
            'FIELD_NAME' => 'UF_ACTIVITY_ID',
            'USER_TYPE_ID' => 'double',
            'XML_ID' => 'UF_ACTIVITY_ID',
            'SORT' => '100',
            'MULTIPLE' => 'N',
            'MANDATORY' => 'N',
            'SHOW_FILTER' => 'N',
            'SHOW_IN_LIST' => 'Y',
            'EDIT_IN_LIST' => 'Y',
            'IS_SEARCHABLE' => 'N',
            'SETTINGS' =>
                array (
                    'PRECISION' => 4,
                    'SIZE' => 20,
                    'MIN_VALUE' => 0.0,
                    'MAX_VALUE' => 0.0,
                    'DEFAULT_VALUE' => 0.0,
                ),
            'EDIT_FORM_LABEL' =>
                array (
                    'en' => '',
                    'ru' => 'UF_ACTIVITY_ID',
                ),
            'LIST_COLUMN_LABEL' =>
                array (
                    'en' => '',
                    'ru' => 'UF_ACTIVITY_ID',
                ),
            'LIST_FILTER_LABEL' =>
                array (
                    'en' => '',
                    'ru' => 'UF_ACTIVITY_ID',
                ),
            'ERROR_MESSAGE' =>
                array (
                    'en' => '',
                    'ru' => '',
                ),
            'HELP_MESSAGE' =>
                array (
                    'en' => '',
                    'ru' => '',
                ),
        ));
        $helper->UserTypeEntity()->saveUserTypeEntity(array (
            'ENTITY_ID' => 'HLBLOCK_CommCompany',
            'FIELD_NAME' => 'UF_CONTACT_ID',
            'USER_TYPE_ID' => 'double',
            'XML_ID' => 'UF_CONTACT_ID',
            'SORT' => '100',
            'MULTIPLE' => 'N',
            'MANDATORY' => 'N',
            'SHOW_FILTER' => 'N',
            'SHOW_IN_LIST' => 'Y',
            'EDIT_IN_LIST' => 'Y',
            'IS_SEARCHABLE' => 'N',
            'SETTINGS' =>
                array (
                    'PRECISION' => 4,
                    'SIZE' => 20,
                    'MIN_VALUE' => 0.0,
                    'MAX_VALUE' => 0.0,
                    'DEFAULT_VALUE' => '',
                ),
            'EDIT_FORM_LABEL' =>
                array (
                    'en' => '',
                    'ru' => 'UF_CONTACT_ID',
                ),
            'LIST_COLUMN_LABEL' =>
                array (
                    'en' => '',
                    'ru' => 'UF_CONTACT_ID',
                ),
            'LIST_FILTER_LABEL' =>
                array (
                    'en' => '',
                    'ru' => 'UF_CONTACT_ID',
                ),
            'ERROR_MESSAGE' =>
                array (
                    'en' => '',
                    'ru' => '',
                ),
            'HELP_MESSAGE' =>
                array (
                    'en' => '',
                    'ru' => '',
                ),
        ));
        $helper->UserTypeEntity()->saveUserTypeEntity(array (
            'ENTITY_ID' => 'HLBLOCK_CommCompany',
            'FIELD_NAME' => 'UF_TITLE',
            'USER_TYPE_ID' => 'string',
            'XML_ID' => 'UF_TITLE',
            'SORT' => '100',
            'MULTIPLE' => 'N',
            'MANDATORY' => 'N',
            'SHOW_FILTER' => 'N',
            'SHOW_IN_LIST' => 'Y',
            'EDIT_IN_LIST' => 'Y',
            'IS_SEARCHABLE' => 'N',
            'SETTINGS' =>
                array (
                    'SIZE' => 20,
                    'ROWS' => 1,
                    'REGEXP' => '',
                    'MIN_LENGTH' => 0,
                    'MAX_LENGTH' => 0,
                    'DEFAULT_VALUE' => '',
                ),
            'EDIT_FORM_LABEL' =>
                array (
                    'en' => '',
                    'ru' => 'UF_TITLE',
                ),
            'LIST_COLUMN_LABEL' =>
                array (
                    'en' => '',
                    'ru' => 'UF_TITLE',
                ),
            'LIST_FILTER_LABEL' =>
                array (
                    'en' => '',
                    'ru' => 'UF_TITLE',
                ),
            'ERROR_MESSAGE' =>
                array (
                    'en' => '',
                    'ru' => '',
                ),
            'HELP_MESSAGE' =>
                array (
                    'en' => '',
                    'ru' => '',
                ),
        ));
        $helper->UserTypeEntity()->saveUserTypeEntity(array (
            'ENTITY_ID' => 'HLBLOCK_CommCompany',
            'FIELD_NAME' => 'UF_DESCRIPTION',
            'USER_TYPE_ID' => 'string',
            'XML_ID' => 'UF_DESCRIPTION',
            'SORT' => '100',
            'MULTIPLE' => 'N',
            'MANDATORY' => 'N',
            'SHOW_FILTER' => 'N',
            'SHOW_IN_LIST' => 'Y',
            'EDIT_IN_LIST' => 'Y',
            'IS_SEARCHABLE' => 'N',
            'SETTINGS' =>
                array (
                    'SIZE' => 20,
                    'ROWS' => 1,
                    'REGEXP' => '',
                    'MIN_LENGTH' => 0,
                    'MAX_LENGTH' => 0,
                    'DEFAULT_VALUE' => '',
                ),
            'EDIT_FORM_LABEL' =>
                array (
                    'en' => '',
                    'ru' => 'UF_DESCRIPTION',
                ),
            'LIST_COLUMN_LABEL' =>
                array (
                    'en' => '',
                    'ru' => 'UF_DESCRIPTION',
                ),
            'LIST_FILTER_LABEL' =>
                array (
                    'en' => '',
                    'ru' => 'UF_DESCRIPTION',
                ),
            'ERROR_MESSAGE' =>
                array (
                    'en' => '',
                    'ru' => '',
                ),
            'HELP_MESSAGE' =>
                array (
                    'en' => '',
                    'ru' => '',
                ),
        ));

    }

    public function down()
    {
        $helper = $this->getHelperManager();

        $helper->Hlblock()->deleteHlblockIfExists('CommCompany');
    }

}
