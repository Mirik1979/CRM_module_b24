<?php

namespace Sprint\Migration;


class tnplan20191009010654 extends Version
{
    protected $description = "Настройки tn.plan";

    public function up()
    {
        $helper = $this->getHelperManager();
        $helper->Option()->saveOption(array (
            'MODULE_ID' => 'tn.plan',
            'NAME' => 'year_for_fact',
            'VALUE' => '2019',
            'DESCRIPTION' => NULL,
            'SITE_ID' => NULL,
        ));
        $helper->Option()->saveOption(array (
            'MODULE_ID' => 'tn.plan',
            'NAME' => 'year_for_the_plan',
            'VALUE' => '2020',
            'DESCRIPTION' => NULL,
            'SITE_ID' => NULL,
        ));
    }

    public function down()
    {
        $helper = $this->getHelperManager();
        $helper->Option()->deleteOptions([
            'MODULE_ID' => 'tn.plan',
            'SITE_ID' => NULL,
            'NAME'=>'year_for_fact',
        ]);
        $helper->Option()->deleteOptions([
            'MODULE_ID' => 'tn.plan',
            'SITE_ID' => NULL,
            'NAME'=>'year_for_the_plan',
        ]);
    }
}
