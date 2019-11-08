<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use Wcomm\CrmStores\Entity\StoreTable;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

class CMainUISelectorComponentAjaxController extends \Bitrix\Main\Engine\Controller
{
	public function getTreeItemRelationAction($entityType = false, $categoryId = false)
	{
		$result = array();

		$event = new Event("main", "OnUISelectorActionProcessAjax", array(
			'action' => 'getTreeItemRelation',
			'requestFields' => array(
				'options' => array(
					'entityType' => $entityType,
					'categoryId' => $categoryId
				),
			)
		));
		$event->send();
		$eventResultList = $event->getResults();

		if (is_array($eventResultList) && !empty($eventResultList))
		{
			foreach ($eventResultList as $eventResult)
			{
				if ($eventResult->getType() == EventResult::SUCCESS)
				{
					$resultParams = $eventResult->getParameters();
					$result = $resultParams['result'];
					break;
				}
			}
		}
        //\Bitrix\Main\Diag\Debug::writeToFile($result, "res2", "__miros.log");
		return $result;
	}

	public function getDataAction(array $options = array(), array $entityTypes = array(), array $selectedItems = array())
	{
        //\Bitrix\Main\Diag\Debug::writeToFile($options, "enttypes222", "__miros.log");
        //\Bitrix\Main\Diag\Debug::writeToFile($entityTypes, "enttypes222", "__miros.log");
        //\Bitrix\Main\Diag\Debug::writeToFile($selectedItems, "enttypes222", "__miros.log");
        $arrret = \Bitrix\Main\UI\Selector\Entities::getData($options, $entityTypes, $selectedItems);

        if($arrret['ENTITIES']['COMPANIES']) {
            if (!Loader::includeModule('wcomm.crmstores')) {
                ShowError(Loc::getMessage('CRMSTORES_NO_MODULE'));
                return;
            } else {
                /*$arrret['ENTITIES']['STORES']['ITEMS'] = array(
                    'S_1' => array(
                        'id' => 'S_1',
                        'entityType' => 'stores',
                        'entityID' => 1,
                        'name' => 'test',
                        'desc' => 'test',
                        'date' => 1566230051
                    ),
                    'S_2' => array(
                        'id' => 'S_2',
                        'entityType' => 'stores',
                        'entityID' => 2,
                        'name' => 'test2',
                        'desc' => 'test2',
                        'date' => 1566230051
                    )
                ); */

                //$arrret['ENTITIES']['STORES']['ITEMS_LAST'] = array("S_1"
                //);

                $params = array
                (
                    'filter' => Array
                    (
                    ),
                    'limit' => 500000,
                    'offset' => 0,
                    'order' => Array
                    (
                        'ID' => desc
                    )

                );
                $stores = StoreTable::getList($params);
                $newstores = $stores->fetchAll();
                foreach($newstores as $storeval) {
                    $storeitem['S_'.$storeval['ID']] = array(
                        'id' => 'S_'.$storeval['ID'],
                        'entityType' => 'stores',
                        'entityID' => $storeval['ID'],
                        'name' => $storeval['NAME'],
                        'desc' => $storeval['NAME'],
                         'date' => 1569399278
                    );

                }
                $arrret['ENTITIES']['STORES']['ITEMS'] = $storeitem;
                //$arrret['ENTITIES']['STORES']['ITEMS_LAST'] = array("S_32"
                //);
                $arrret['ENTITIES']['STORES']['ADDITIONAL_INFO'] = array(
                    'GROUPS_LIST' => array(
                       'crmstores' => array(
                           'TITLE' => 'Объекты',
                           'TYPE_LIST' => array('STORES'),
                           'DESC_LESS_MODE' => 'N',
                           'SORT' => 40

                       ),
                       'SORT_SELECTED' => 400

                    )


                );
                $arrret['TABS']['stores'] = array(
                    'id' => 'stores',
                    'name' => 'Объекты',
                    'sort' => 10
                );
            }

        }



        //\Bitrix\Main\Diag\Debug::writeToFile($arrret, "res1", "__miros.log");
        return $arrret;
	    //return \Bitrix\Main\UI\Selector\Entities::getData($options, $entityTypes, $selectedItems);
	}

	public function doSearchAction($searchString = '', $searchStringConverted = '', $currentTimestamp = 0, array $options = array(), array $entityTypes = array(), array $additionalData = array())
	{
        //\Bitrix\Main\Diag\Debug::writeToFile($options, "options", "__miros.log");
        //\Bitrix\Main\Diag\Debug::writeToFile($entityTypes, "types", "__miros.log");
        //\Bitrix\Main\Diag\Debug::writeToFile($searchString, "string", "__miros.log");
        //\Bitrix\Main\Diag\Debug::writeToFile($searchStringConverted, "search", "__miros.log");
        //\Bitrix\Main\Diag\Debug::writeToFile($additionalData, "data", "__miros.log");


	    $result = \Bitrix\Main\UI\Selector\Entities::search($options, $entityTypes, array(
			'searchString' => $searchString,
			'searchStringConverted' => $searchStringConverted,
			'additionalData' => $additionalData
		));

        /*if (!Loader::includeModule('wcomm.crmstores')) {
            ShowError(Loc::getMessage('CRMSTORES_NO_MODULE'));
            return;
        } else {
            $params = array
            (
                'filter' => Array
                (
                ),
                'limit' => 20,
                'offset' => 0,
                'order' => Array
                (
                    'ID' => desc
                )

            );
            $stores = StoreTable::getList($params);
            $newstores = $stores->fetchAll();
            foreach($newstores as $storeval) {
                $storeitem['S_'.$storeval['ID']] = array(
                    'id' => 'S_'.$storeval['ID'],
                    'entityType' => 'stores',
                    'entityID' => $storeval['ID'],
                    'name' => $storeval['NAME'],
                    'desc' => $storeval['NAME'],
                    'date' => $currentTimestamp
                );

            }
            $result['ENTITIES']['STORES']['ITEMS'] = $storeitem;
            $result['ENTITIES']['STORES']['ADDITIONAL_INFO'] = array();
            //$result['ENTITIES']['STORES']['ITEMS'] = $storeitem;
            //$result['ENTITIES']['STORES']['ADDITIONAL_INFO'] = array();

        } */



		$result['currentTimestamp'] = $currentTimestamp;
        // \Bitrix\Main\Diag\Debug::writeToFile($result, "res3", "__miros.log");
		return $result;
	}

	public function loadAllAction($entityType)
	{

        //$arrttt = \Bitrix\Main\UI\Selector\Entities::loadAll($entityType);
        //\Bitrix\Main\Diag\Debug::writeToFile($arrttt, "res4", "__miros.log");

        return \Bitrix\Main\UI\Selector\Entities::loadAll($entityType);
	}

	public function saveDestinationAction($context, $itemId)
	{
		if (
			!empty($context)
			&& !empty($itemId)
		)
		{
			\Bitrix\Main\UI\Selector\Entities::save([
				'context' => $context,
				'code' => $itemId
			]);
		}
	}

}
