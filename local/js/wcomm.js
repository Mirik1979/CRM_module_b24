wcomm_PopupWindowCA = null;
wcomm_PopupWindowCA2 = null;
wcomm_CALL_OWNER_TYPE_ID = null;
wcomm_CALL_OWNER_ID = null;
wcomm_COMPANY_ID = null;

WCOMM_CALL_BIZ_PROC_ID = 20;


BX.addCustomEvent('onAfterActivitySave', function(params)
{

	var request = BX.ajax.runAction('wcomm:callmodifications.api.activityajax.callsexist', {
		data: {
			param1: '1'
		}
	});
		 
	request.then(function(response){
		
		//console.log("=3333=");	
			
			if(('status' in response)&&(response['status'] == 'success')&&('data' in response)&&('show' in response['data']))
			{
				
				if(response['data']['show'] == 'true')
				{
					wcomm_CALL_OWNER_TYPE_ID = response['data']['OWNER_TYPE_ID'];
					wcomm_CALL_OWNER_ID = response['data']['OWNER_ID'];
					wcomm_COMPANY_ID = response['data']['COMPANY_ID'];
					
					wcomm_PopupWindowCA = new BX.PopupWindow(
						"CallAddPopupWindow",
						null,
						{
							"zIndex": 90000,
							"closeByEsc": true,
							"autoHide": false,
							"offsetLeft": -50,
							"closeIcon": false,
							"overlay": {backgroundColor: 'black', opacity: '80' },
							"className": "crm-list-end-deal",
							"content": CallMeetingPopupWindowPrepareContent() //,
							
						}
					);
					
					wcomm_PopupWindowCA.show();
				}
			}
			
	});
	
});


function CallMeetingPopupWindowPrepareContent()
{
	WrapperPC = BX.create("DIV");
	
	var table = BX.create("TABLE",
		{
			attrs: { className: "crm-list-end-deal-block" },
			props: { cellSpacing: "0", cellPadding: "0", border: "0" }
		}
	);
	WrapperPC.appendChild(table);

	var cell = table.insertRow(-1).insertCell(-1);
	cell.className = "crm-list-end-deal-text";
	cell.innerHTML = BX.message('CRM_WCOMM_CREATE_TITLE');

	cell = table.insertRow(-1).insertCell(-1);
	cell.className = "crm-list-end-deal-buttons-block";

	
	if(true)
	{
		var successText = BX.message('CRM_WCOMM_CREATE_CALL');
		var successButton = BX.create(
			"A",
			{
				attrs: { className: "webform-small-button webform-small-button-accept", href: "#" },
				children:
				[
						BX.create("SPAN", { attrs: { className: "webform-small-button-left" } }),
						BX.create("SPAN", { attrs: { className: "webform-small-button-text" }, text: successText }),
						BX.create("SPAN", { attrs: { className: "webform-small-button-right" } })
				]
			}
		);
					
		cell.appendChild(successButton);
		var successId = "successID";
		BX.CrmSubscriber.subscribe(
			'2322_' + successId,
			successButton,
			"click",
			function()
			{
					//"CALL";
					planner = new BX.Crm.Activity.Planner();
					planner.showEdit(
						{
							"TYPE_ID": BX.CrmActivityType.call,
							"OWNER_TYPE_ID": wcomm_CALL_OWNER_TYPE_ID, //BX.CrmEntityType.enumeration.deal,
							"OWNER_ID": wcomm_CALL_OWNER_ID
						}
					);
							
					wcomm_PopupWindowCA.close();
						
			} ,
			BX.CrmParamBag.create({ id: successId, preventDefault: true })
		);
	}
			

	if(true)
	{

		failureTitle = BX.message('CRM_WCOMM_CREATE_MEETING');
		var failureButton = BX.create(
				"A",
				{
					//"webform-small-button webform-small-button-decline"
					attrs: { className: "webform-small-button webform-small-button-accept", href: "#" },
					children:
					[
						BX.create("SPAN", { attrs: { className: "webform-small-button-left" } }),
						BX.create("SPAN", { attrs: { className: "webform-small-button-text" }, text: failureTitle }),
						BX.create("SPAN", { attrs: { className: "webform-small-button-right" } })
					]
				}
		);
		cell.appendChild(failureButton);
		var failureId = "failureID";
		BX.CrmSubscriber.subscribe(
			'2322_' + failureId,
			failureButton, 
			"click", 
			function()
			{
					//"MEETING";
					planner = new BX.Crm.Activity.Planner();
					planner.showEdit(
						{
							"TYPE_ID": BX.CrmActivityType.meeting,
							"OWNER_TYPE_ID": wcomm_CALL_OWNER_TYPE_ID, //BX.CrmEntityType.enumeration.deal,
							"OWNER_ID": wcomm_CALL_OWNER_ID
						}
					);
					
					wcomm_PopupWindowCA.close();
						
			},
			BX.CrmParamBag.create({ id: failureId, preventDefault: true })
		);
	}
	
	

	if(true)
	{

		failureTitle = BX.message('CRM_WCOMM_TO_ARCHIVE');
		var failureButton = BX.create(
				"A",
				{
					//"webform-small-button webform-small-button-decline"
					attrs: { className: "webform-small-button webform-small-button-accept", href: "#" },
					children:
					[
						BX.create("SPAN", { attrs: { className: "webform-small-button-left" } }),
						BX.create("SPAN", { attrs: { className: "webform-small-button-text" }, text: failureTitle }),
						BX.create("SPAN", { attrs: { className: "webform-small-button-right" } })
					]
				}
		);
		cell.appendChild(failureButton);
		var failureId = "failureID";
		BX.CrmSubscriber.subscribe(
			'2322_' + failureId,
			failureButton, 
			"click", 
			function()
			{
				
				wcomm_PopupWindowCA.close();


				var request = BX.ajax.runAction('wcomm:callmodifications.api.activityajax.archivecampaign', {
					data: {
						CompanyID: wcomm_COMPANY_ID
					}
				});
					 
				request.then(function(response){
					
					if(('status' in response)&&(response['status'] == 'success'))
					{
						
						if(('data' in response)&&('ok' in response['data'])&&(response['data']['ok'] == 'true'))
						{
							BX.ajax({
								url: "/bitrix/components/bitrix/bizproc.workflow.start/ajax.php",
								data: {
									"template_id": WCOMM_CALL_BIZ_PROC_ID,
									"sessid": BX.bitrix_sessid(),
									"ajax_action": "start_workflow",
									"module_id": "crm",
									"entity": "CCrmDocumentCompany",
									"document_type": "COMPANY",
									"document_id": "COMPANY_" + wcomm_COMPANY_ID,
								},
								method: 'POST',
								dataType: 'json',
								timeout: 30,
								async: true,
								processData: true,
								scriptsRunFirst: true,
								emulateOnload: true,
								start: true,
								cache: false,
								onsuccess: function(data2){
									
									ShowArchivePopupWindow(true);
									
								},
								onfailure: function(){
									
									ShowArchivePopupWindow(false);
									
								}
							});
							
							return;
						}
						
						
					}
					
					ShowArchivePopupWindow(false);
					
				});
				
			},
			BX.CrmParamBag.create({ id: failureId, preventDefault: true })
		);
	}
			
			
	return WrapperPC;
}

function ShowArchivePopupWindow(success)
{
	wcomm_PopupWindowCA2 = new BX.PopupWindow(
		"CallAddPopupWindow",
		null,
		{
			"zIndex": 90001,
			"closeByEsc": true,
			"autoHide": false,
			"offsetLeft": -50,
			"closeIcon": false,
			"overlay": {backgroundColor: 'black', opacity: '80' },
			"className": "crm-list-end-deal",
			"content": ArchivePopupWindowPrepareContent(success)							
		}
	);

	wcomm_PopupWindowCA2.show();
}

function ArchivePopupWindowPrepareContent(success)
{
	WrapperPC = BX.create("DIV");
	
	var table = BX.create("TABLE",
		{
			attrs: { className: "crm-list-end-deal-block" },
			props: { cellSpacing: "0", cellPadding: "0", border: "0" }
		}
	);
	WrapperPC.appendChild(table);

	var cell = table.insertRow(-1).insertCell(-1);
	cell.className = "crm-list-end-deal-text";
	
	if(success)
	{
		cell.innerHTML = BX.message('CRM_WCOMM_TO_ARCHIVE_OK');
	} else {
		cell.innerHTML = BX.message('CRM_WCOMM_TO_ARCHIVE_ERR');
	}

	cell = table.insertRow(-1).insertCell(-1);
	cell.className = "crm-list-end-deal-buttons-block";

	
	if(true)
	{
		var successText = BX.message('CRM_WCOMM_CLOSE_BUTTON');
		var successButton = BX.create(
			"A",
			{
				attrs: { className: "webform-small-button webform-small-button-accept", href: "#" },
				children:
				[
						BX.create("SPAN", { attrs: { className: "webform-small-button-left" } }),
						BX.create("SPAN", { attrs: { className: "webform-small-button-text" }, text: successText }),
						BX.create("SPAN", { attrs: { className: "webform-small-button-right" } })
				]
			}
		);
					
		cell.appendChild(successButton);
		var successId = "successID";
		BX.CrmSubscriber.subscribe(
			'2323_' + successId,
			successButton,
			"click",
			function()
			{							
					wcomm_PopupWindowCA2.close();
						
			} ,
			BX.CrmParamBag.create({ id: successId, preventDefault: true })
		);
	}
			
			
			
	return WrapperPC;
}



