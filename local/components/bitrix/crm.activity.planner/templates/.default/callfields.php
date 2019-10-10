<?


class CallFields
{
	
	const IBLOCK_CODE = "CallResult";
	
	const HLiBLOCK_NAME = "CallResults";
	
	const CALL_FIELDS_DIR_NAME = "CALL_FIELDS_DIR";
	const CALL_FIELDS_STR_NAME = "CALL_FIELDS_STR";
	
	const CALL_ENABLE_CHECKBOX = false;
	
		
	static function EchoCompletedForSliderEdit($activity)
	{
		
		if(self::CALL_ENABLE_CHECKBOX || (((int)$activity['TYPE_ID'] !== \CCrmActivityType::Call) && ((int)$activity['TYPE_ID'] !== \CCrmActivityType::Meeting)))
		{
			?>
							<label class="crm-activity-popup-timeline-checkbox-block">
								<input type="checkbox" name="completed" value="Y" class="crm-activity-popup-timeline-checkbox" <?if ($activity['COMPLETED'] == 'Y'):?>checked<?endif?>>
								<span class="crm-activity-popup-timeline-checkbox-text"><?=GetMessage('CRM_ACTIVITY_PLANNER_COMPLETED_SLIDER')?></span>
							</label>
			<?
			
		} else {
			
			?>
							<label class="crm-activity-popup-timeline-checkbox-block">
								<span class="crm-activity-popup-timeline-checkbox-text"><?
								
								if($activity["COMPLETED"] == "Y")
								{
									echo GetMessage("WCOMM_CALLMODIFICATIONS_HLIBLOCK_COMPLETED");
								} else {
									echo GetMessage("WCOMM_CALLMODIFICATIONS_HLIBLOCK_UNCOMPLETED");
								}
								
								?></span>
							</label>
			<?
			
		}
	
	}
	
	static function EchoCompletedForSliderButton($activity,$provider)
	{
		
		if(self::CALL_ENABLE_CHECKBOX || ($activity["COMPLETED"] != "Y") || (((int)$activity['TYPE_ID'] !== \CCrmActivityType::Call) && ((int)$activity['TYPE_ID'] !== \CCrmActivityType::Meeting)))
		{
			
			if($provider::isTypeEditable($activity['PROVIDER_TYPE_ID'], $activity['DIRECTION']))
			{
				
						?><button class="webform-small-button webform-small-button-accept" data-role="button-edit">
							<span class="webform-small-button-text"><?=GetMessage('CRM_ACTIVITY_PLANNER_EDIT')?></span>
						</button><?
			}
			
		}
		
	}
	
	static function EchoCompletedForEdit($activity)
	{
		
		if(self::CALL_ENABLE_CHECKBOX || (((int)$activity['TYPE_ID'] !== \CCrmActivityType::Call) && ((int)$activity['TYPE_ID'] !== \CCrmActivityType::Meeting)))
		{
			?>
							<label class="crm-activity-popup-timeline-checkbox-block">
								<input type="checkbox" name="completed" value="Y" class="crm-activity-popup-timeline-checkbox" <?if ($activity['COMPLETED'] == 'Y'):?>checked<?endif?>>
								<span class="crm-activity-popup-timeline-checkbox-text"><?=GetMessage('CRM_ACTIVITY_PLANNER_CHECK_COMPLETED_2')?></span>
							</label>
			<?
			
		} else {
			
			?>
							<label class="crm-activity-popup-timeline-checkbox-block">
								<span class="crm-activity-popup-timeline-checkbox-text"><?
								
								if($activity["COMPLETED"] == "Y")
								{
									echo GetMessage("WCOMM_CALLMODIFICATIONS_HLIBLOCK_COMPLETED");
								} else {
									echo GetMessage("WCOMM_CALLMODIFICATIONS_HLIBLOCK_UNCOMPLETED");
								}
								
								?></span>
							</label>
			<?
			
		}
	
	}
	
	static function EchoCompletedForSliderView($activity)
	{
		
		if(self::CALL_ENABLE_CHECKBOX || (((int)$activity['TYPE_ID'] !== \CCrmActivityType::Call) && ((int)$activity['TYPE_ID'] !== \CCrmActivityType::Meeting)))
		{
			?>
							<input class="crm-activity-planner-slider-header-control-checkbox" type="checkbox" id="<?=($inputId = uniqid('inp_')) ?>" data-role="field-completed" <? if ($activity['COMPLETED'] == 'Y'): ?> checked<? endif ?>>
							<label class="crm-activity-planner-slider-header-control-text crm-activity-planner-slider-header-control-label" for="<?=$inputId ?>"><?=getMessage('CRM_ACTIVITY_PLANNER_COMPLETED_SLIDER') ?></label>
						
			<?
			
		} else {
			
			?>
							<label class="crm-activity-planner-slider-header-control-text crm-activity-planner-slider-header-control-label">
								<?
								
								if($activity["COMPLETED"] == "Y")
								{
									echo GetMessage("WCOMM_CALLMODIFICATIONS_HLIBLOCK_COMPLETED");
								} else {
									echo GetMessage("WCOMM_CALLMODIFICATIONS_HLIBLOCK_UNCOMPLETED");
								}
								
								?>
							</label>
			<?
			
		}
	
	}
	
	static function _EchoCompletedForView($activity)
	{
		
		if(self::CALL_ENABLE_CHECKBOX || (((int)$activity['TYPE_ID'] !== \CCrmActivityType::Call) && ((int)$activity['TYPE_ID'] !== \CCrmActivityType::Meeting)))
		{
			?>
						<input type="checkbox" class="crm-task-list-extra-checkbox" data-role="field-completed"<?if ($activity['COMPLETED'] == 'Y'):?> checked<?endif?>>
						<span class="crm-task-list-extra-text"><?=GetMessage('CRM_ACTIVITY_PLANNER_CHECK_COMPLETED_2')?></span>
						
			<?
			
		} else {
			
			?>
							<span class="crm-task-list-extra-text">
								<?
								
								if($activity["COMPLETED"] == "Y")
								{
									echo GetMessage("WCOMM_CALLMODIFICATIONS_HLIBLOCK_COMPLETED");
								} else {
									echo GetMessage("WCOMM_CALLMODIFICATIONS_HLIBLOCK_UNCOMPLETED");
								}
								
								?>
							</span>
			<?
			
		}
	
	}
	
	static function EchoFields($activity)
	{
		
		if(((int)$activity['TYPE_ID'] !== \CCrmActivityType::Call) && ((int)$activity['TYPE_ID'] !== \CCrmActivityType::Meeting))
		{
			return;
		}
		
		
		$FieldsDirID = 0;
		$FieldsStr = "";
		$strSelect = "selected";
		$arrR = self::GetHLiBlockID($activity["ID"]);
		if($arrR != null)
		{
			$FieldsDirID = $arrR["UF_RESULT_ID"];
			$FieldsStr = $arrR["UF_RESULT_STRING"];
			$strSelect = "";
		}
		
		echo '<div class="crm-activity-popup-info-person-detail-calendar">
													<label class="crm-activity-popup-info-person-detail-calendar-name">' . GetMessage('WCOMM_CALLMODIFICATIONS_HLIBLOCK_RESULT') . ':</label>
													<select name="' . self::CALL_FIELDS_DIR_NAME . '" class="crm-activity-popup-info-person-detail-calendar-input" data-role="field-direction">';
		
		$arSelect = Array("ID", "NAME");
		$arFilter = Array("IBLOCK_CODE"=>self::IBLOCK_CODE, "ACTIVE_DATE"=>"Y", "ACTIVE"=>"Y");
		$res = CIBlockElement::GetList(Array(), $arFilter, false, Array("nPageSize"=>50), $arSelect);
					
		echo '<option value="0" ' . $strSelect . '>---</option>';
		
		while($ob = $res->GetNextElement())
		{
			$arFields = $ob->GetFields();
			
			$strSelect = "";
			if($arFields["ID"] == $FieldsDirID)
			{
				$strSelect = "selected";
			}
									
			echo '<option value="' . $arFields["ID"] . '" ' . $strSelect . '>' . $arFields["NAME"] . '</option>';
			
		}
		
		echo '</select>
													</div>
													<div class="crm-activity-popup-info-person-detail-calendar">
										<label class="crm-activity-popup-info-person-detail-description-name">' . GetMessage('WCOMM_CALLMODIFICATIONS_HLIBLOCK_RESULT_COMMENT') . ':</label>
										<textarea name="' . self::CALL_FIELDS_STR_NAME . '" class="crm-activity-popup-info-person-detail-description-input">' . htmlspecialcharsbx($FieldsStr) . '</textarea>
										</div>';

	}
	
	static function GetHLiBlockID($id)
	{
			
		\Bitrix\Main\Loader::IncludeModule("highloadblock");
		
		$result = \Bitrix\Highloadblock\HighloadBlockTable::getList(array('filter'=>array('=NAME'=>self::HLiBLOCK_NAME)));
		if($row = $result->fetch())
		{
									
			$hlblock = \Bitrix\Highloadblock\HighloadBlockTable::getById($row["ID"])->fetch();
			$entity = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlblock);
			$entityDataClass = $entity->getDataClass();
			
						
			$result2 = $entityDataClass::getList(array(
				"select" => array("*"),
				"order" => array("ID" => "DESC"),
				"filter" => Array("UF_CALL_ID" => $id),
			));
			
			if($arRow = $result2->Fetch())
			{
				return $arRow;
			}
			
		}
		
		return null;
		
	}
}





	
	