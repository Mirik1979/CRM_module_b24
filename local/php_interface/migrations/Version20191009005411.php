<?php

namespace Sprint\Migration;


class Version20191009005411 extends Version
{
    protected $description = "Элементы Сегменты планирования";

    /**
     * @throws Exceptions\ExchangeException
     * @throws Exceptions\RestartException
     * @return bool|void
     */
    public function up()
    {
        $this->getExchangeManager()
            ->IblockElementsImport()
            ->setExchangeResource('iblock_elements.xml')
            ->setLimit(20)
            ->execute(function ($item) {
                $this->getHelperManager()
                    ->Iblock()
                    ->addElement(
                        $item['iblock_id'],
                        $item['fields'],
                        $item['properties']
                    );
            });
    }

    /**
     * @return bool|void
     * @throws Exceptions\HelperException
     */
    public function down()
    {
        $helper = $this->getHelperManager();
        $IblockId=$helper->Iblock()->getIblockId('planning_segments','lists');
        $list=$helper->Iblock()->getElements($IblockId);
        foreach ($list as $val)
            $helper->Iblock()->deleteElement($val["ID"]);
    }
}