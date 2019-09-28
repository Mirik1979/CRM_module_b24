wcomm_PopupWindowCA = null;
wcomm_CALL_OWNER_TYPE_ID = null;
wcomm_CALL_OWNER_ID = null;

//console.log("=1111=");

BX.addCustomEvent('onAfterActivitySave', function(params)
{
	
	//console.log("=2222=");
		
	//BX.CrmTimelineMenuBar.openMenu();
	//BX.message('CRM_TIMELINE_SMS_REST_MARKETPLACE')
	//BX.SidePanel.Instance.open
		
		
	var request = BX.ajax.runAction('wcomm:callmodifications.api.activityajax.callsexist', {
		data: {
			param1: '1'
		}
	});
		 
	request.then(function(response){
		
		console.log("=3333=");	
			
			if(('status' in response)&&(response['status'] == 'success')&&('data' in response)&&('data' in response)&&('show' in response['data']))
			{
				
				if(response['data']['show'] == 'true')
				{
					wcomm_CALL_OWNER_TYPE_ID = response['data']['OWNER_TYPE_ID'];
					wcomm_CALL_OWNER_ID = response['data']['OWNER_ID'];
					
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
							"content": CallAddPopupWindowPrepareContent() //,
							
						}
					);
					
					wcomm_PopupWindowCA.show();
				}
			}
			
	});
	
});


function CallAddPopupWindowPrepareContent()
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
				successButton, "click", function()
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
			failureButton, "click", function()
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
			
			
	return WrapperPC;
}