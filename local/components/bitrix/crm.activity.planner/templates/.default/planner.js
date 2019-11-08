
BX.ready(function(){

    var CRM_ACTIVITY_PLANNER_ID_ATTRIBUTE = 'data-crm-act-planner';
    var DEFAULT_AJAX_URL = '/local/components/bitrix/crm.activity.planner/ajax.php?site_id=' + BX.message('SITE_ID');
    var COMMUNICATIONS_AJAX_URL = '/local/components/bitrix/crm.activity.editor/ajax.php?siteID='+BX.message('SITE_ID')+'&sessid='+BX.bitrix_sessid();
    var COMMUNICATIONS_AJAX_URL_CONTACT = '/local/components/bitrix/crm.activity.editor/ajax.php?useContact=Y&siteID='+BX.message('SITE_ID')+'&sessid='+BX.bitrix_sessid();
    var COMMUNICATIONS_AJAX_URL_STORE = '/local/components/bitrix/crm.activity.editor/ajax.php?useStore=Y&siteID='+BX.message('SITE_ID')+'&sessid='+BX.bitrix_sessid();
    var Planner=BX.Crm.Activity.Planner;
    var Destination=BX.Crm.Activity.Destination;

    Planner.prototype.prepareEditLayout = function(editorNode)
    {
        var me = this, popup = me.getPopup();
        if (!editorNode) //show error
        {
            if (popup)
            {
                popup.show();
            }
            return;
        }

        var providerId = this.getNodeValue('field-provider-id');

        me.synchronizeViewModeState();
        BX.bind(me.getNode('view-mode-switcher'), 'click', function(){me.onViewModeClick(this)});
        BX.bind(me.getNode('additional-mode-switcher'), 'click', function(){me.onAdditionalModeClick(this)});

        BX.bind(me.getNode('priority-switcher'), 'click', function(){
            BX.toggleClass(me.getNode('priority-flame'), 'crm-activity-popup-container-open');
            return false;
        });

        var i, s, daySwitcher = me.getNode('day-switcher');
        for (i = 0, s = daySwitcher.childNodes.length; i < s; ++i)
        {
            BX.bind(daySwitcher.childNodes[i], 'click', function(){me.onDaySwitchClick(this)});
        }

        BX.bind(me.getNode('notify-activator'), 'change', function(){me.onNotifyActivatorChange(this)});
        BX.bind(me.getNode('notify-switcher'), 'click', function(){me.onNotifyChangeClick(this)});

        me.setNotify(me.getNode('field-notify-value').value, me.getNode('field-notify-type').value);

        var onDateFieldClick = function() {
            BX.calendar({ node: this, field: this, bTime: false});
            return false;
        };

        BX.bind(me.getNode('calendar-start-time'), 'click', onDateFieldClick);
        BX.bind(me.getNode('calendar-end-time'), 'click', onDateFieldClick);

        BX.bind(me.getNode('clock-start-time'), 'click', function(){me.onTimeSwitchClick(this)});
        BX.bind(me.getNode('clock-end-time'), 'click', function(){me.onTimeSwitchClick(this)});

        BX.bind(me.getNode('calendar-start-time'), 'change', function(){me.updateStartTime();});
        BX.bind(me.getNode('clock-start-time'), 'change', function(){me.updateStartTime();});
        BX.bind(me.getNode('calendar-end-time'), 'change', function(){me.updateEndTime();});
        BX.bind(me.getNode('clock-end-time'), 'change', function(){me.updateEndTime();});

        BX.bind(me.getNode('duration-value'), 'change', function(){me.recalculateEndTime();});
        BX.bind(me.getNode('duration-type'), 'change', function(){me.recalculateEndTime();});

        var storageSwitcher = me.getNode('storage-switcher');
        if (storageSwitcher)
        {
            var storageType = parseInt(storageSwitcher.getAttribute('data-storage-type'));
            var storageValues = JSON.parse(storageSwitcher.getAttribute('data-values'));
            // var storageProps = JSON.parse(storageSwitcher.getAttribute('data-props'));

            if (storageType === Planner.util.storageType.Disk)
            {
                me.createDiskUploader(storageValues, me.getNode('storage-container'));
            }
            else
            {
                BX.hide(storageSwitcher);
                BX.hide(me.getNode('storage-container'));
            }
        }

        var destinationEntities = JSON.parse(me.getNode('destination-entities').value);

        var destinationContainerTpl = me.getNode('template-destination-container');
        var destinationItemTpl = me.getNode('template-destination-item');

        var dealContainerNode = me.getNode('deal-container');
        if (dealContainerNode)
        {
            me.dealDestination = new Destination(
                dealContainerNode,
                'deal',
                {
                    containerTpl: destinationContainerTpl,
                    itemTpl: destinationItemTpl,
                    valueInputName: 'dealId',
                    selected: destinationEntities.deal,
                    selectOne: true
                }
            );
        }
        var orderContainerNode = me.getNode('order-container');
        if (orderContainerNode)
        {
            me.orderDestination = new Destination(
                orderContainerNode,
                'order',
                {
                    containerTpl: destinationContainerTpl,
                    itemTpl: destinationItemTpl,
                    valueInputName: 'orderId',
                    selected: destinationEntities.order,
                    selectOne: true
                }
            );
        }

        var responsibleContainernode = me.getNode('responsible-container');
        if (responsibleContainernode)
        {
            me.responsibleDestination = new Destination(
                me.getNode('responsible-container'),
                'responsible',
                {
                    containerTpl: destinationContainerTpl,
                    itemTpl: destinationItemTpl,
                    valueInputName: 'responsibleId',
                    selected: destinationEntities.responsible,
                    selectOne: true,
                    required: true,
                    events: {
                        select: function(params)
                        {
                            me.checkPlannerState(params);
                        }
                    }
                }
            );
        }

        var communicationsNode = me.getNode('communications-container');
        if (communicationsNode)
        {
            var commtype;
            var commvalue;

            commtype = me.getNode('communications-type').value;
            if (commtype =='STORE')
            {
               commvalue ='';
            } else {
                commvalue = JSON.parse(me.getNode('communications-data').value);
            }


            me.communications = new Communications(
                communicationsNode,
                {
                    selectCallback: BX.delegate(function (communication) {

                        me.communications2.setEnabled();
                        me.communications2.setSelectCompany(communication.getSettings());
                        me.dealDestination.setEnabled();
                        me.dealDestination.setSelectCompany(communication.getSettings());
                    }, me),
                    entityType: me.getNodeValue('field-owner-type'),
                    entityId: me.getNodeValue('field-owner-id'),
                    containerTpl: destinationContainerTpl,
                    itemTpl: destinationItemTpl,
                    //selected: JSON.parse(me.getNode('communications-data').value),
                    selected: commvalue,
                    //TODO: [tag: MEETING_MULTIPLE] replace rule in comment below to apply Meeting multiple communications
                    selectOne: true,//(providerId !== 'CRM_MEETING'),
                    communicationType: me.getNode('communications-container').getAttribute('data-communication-type')
                }
            );
        }

        var communicationsNode2 = me.getNode('communications2-container');
        if (communicationsNode2)
        {
            me.communications2 = new Contacts(
                communicationsNode2,
                {
                    entityType: me.getNodeValue('field-owner-type'),
                    entityId: me.getNodeValue('field-owner-id'),
                    containerTpl: destinationContainerTpl,
                    itemTpl: destinationItemTpl,
                    selected: JSON.parse(me.getNode('communications-data2').value),
                    //TODO: [tag: MEETING_MULTIPLE] replace rule in comment below to apply Meeting multiple communications
                    selectOne: true,//(providerId !== 'CRM_MEETING'),
                    communicationType: me.getNode('communications2-container').getAttribute('data-communication-type')
                }
            );
            if(me.communications) {
                var val=JSON.parse(me.getNode('communications-data').value);
                me.communications2.setSelectCompany(val[0]);
                me.dealDestination.setSelectCompany(val[0]);
                //if(typeof (val[0])=="undefined")
                 //   me.dealDestination.setDisabled();

            }

        }

        var communicationsNode3 = me.getNode('communications3-container');
        if (communicationsNode3)
        {
            commtype = me.getNode('communications-type').value;
            if (commtype =='STORE')
            {
                commvalue = JSON.parse(me.getNode('communications-data').value);
            } else {
                commvalue ='';
            }

            me.communications3 = new Stores(
                communicationsNode3,
                {
                    entityType: me.getNodeValue('field-owner-type'),
                    entityId: me.getNodeValue('field-owner-id'),
                    containerTpl: destinationContainerTpl,
                    itemTpl: destinationItemTpl,
                    //selected: JSON.parse(me.getNode('communications-data').value),
                    selected: commvalue,
                    //TODO: [tag: MEETING_MULTIPLE] replace rule in comment below to apply Meeting multiple communications
                    selectOne: true,//(providerId !== 'CRM_MEETING'),
                    communicationType: me.getNode('communications3-container').getAttribute('data-communication-type')
                }
            );
            if(me.communications) {
                var val=JSON.parse(me.getNode('communications-data').value);
                me.communications2.setSelectCompany(val[0]);
                me.dealDestination.setSelectCompany(val[0]);
                //if(typeof (val[0])=="undefined")
                //   me.dealDestination.setDisabled();

            }
        }

        if (popup)
        {
            popup.show();
        }

        //after show
        var focusInput = me.getNode('focus-on-show');
        if (focusInput)
            BX.defer(BX.focus)(focusInput);

        me.refreshDateTimeView();
    };


    Planner.prototype.saveActivity = function()
    {
        var i, me = this;

        var startTime = me.getStartTime();
        var endTime = me.getEndTime();
        var providerType = this.getNodeValue('field-provider-type-id');

        if (startTime && endTime && startTime.getTime() > endTime.getTime())
        {
            this.showError(BX.message('CRM_ACTIVITY_PLANNER_DATES_ERR'));
            return;
        }

        if (this.saveInProgress)
            return;

        this.saveInProgress = true;

        var activityData = BX.ajax.prepareForm(this.getNode('form')).data;

        var storageSwitcher = me.getNode('storage-switcher');
        if (storageSwitcher)
        {
            var storageType = parseInt(storageSwitcher.getAttribute('data-storage-type'));

            if (storageType === Planner.util.storageType.Disk && this.diskUploader)
            {
                activityData['storageTypeID'] = Planner.util.storageType.Disk;
                activityData['diskfiles'] = this.diskUploader.getFileIds();
            }
            else
            {
                activityData['disableStorageEdit'] = 'Y';
            }
        }

        activityData['communications'] = me.communications ? me.communications.items : [];
        activityData['communications2'] = me.communications2 ? me.communications2.items : [];

        var hasOwner = activityData['dealId'] || activityData['ownerId'] && activityData['ownerType'];
        if (!hasOwner)
        {
            for (i = 0; i < activityData['communications'].length; ++i)
            {
                if (activityData['communications'][i]['entityId'] > 0)
                {
                    hasOwner = true;
                    break;
                }
            }
        }

        if (!hasOwner && providerType !== 'CALL_LIST')
        {
            this.showError(BX.message('CRM_ACTIVITY_PLANNER_NO_OWNER'));
            me.saveInProgress = false;
            return;
        }

        BX.ajax({
            method: 'POST',
            dataType: 'json',
            url: DEFAULT_AJAX_URL,
            data: {
                ajax_action: 'ACTIVITY_SAVE',
                data: activityData,
                sessid: BX.bitrix_sessid()
            },
            onsuccess: function (response)
            {
                me.saveInProgress = false;
                if (response.SUCCESS)
                {
                    if (me.getPopup())
                    {
                        me.getPopup().close();
                    }
                    BX.onCustomEvent(me, 'onAfterActivitySave', [response.DATA.ACTIVITY]);
                    Planner.Manager.fireEvent('onAfterActivitySave', {}, me);
                }
                else
                {
                    me.showError(response.ERRORS[0]);
                }
            }
        });
    };





    // -> Destination
    var Destination = function(container, type, config)
    {
        var me = this, tagNode;
        if (!config)
            config = {};
        this.disabled=false;
        this.bindContainer = container;
        this.ajaxUrl = config.ajaxUrl || DEFAULT_AJAX_URL;
        this.itemTpl = config.itemTpl;

        this.contactid=0;

        this.data = null;
        this.type = type;
        this.dialogId = 'crm-aw-dest-' + type + ('' + new Date().getTime()).substr(6);
        this.valueInputName = config.valueInputName || '';
        this.selected = config.selected ? BX.clone(config.selected) : [];
        this.crmTypes = config.crmTypes;
        this.selectOne = config.selectOne || false;
        this.required = config.required || false;
        this.events = config.events || {};

        this.bindContainer.appendChild(BX.clone(config.containerTpl));
        tagNode = this.getNode('destination-tag');

        BX.bind(tagNode, 'focus', function(e) {
            me.openDialog({bByFocusEvent: true});
            return BX.PreventDefault(e);
        });
        BX.bind(this.bindContainer, 'click', function(e) {
            me.openDialog();
            return BX.PreventDefault(e);
        });

        this.addItems(this.selected);

        tagNode.innerHTML = (
            this.selected.length <= 0
                ? BX.message('CRM_ACTIVITY_PLANNER_DEST_1')
                : BX.message('CRM_ACTIVITY_PLANNER_DEST_2')
        );
    };

    Destination.prototype.getNode = function(name, scope)
    {
        if (!scope)
            scope = this.bindContainer;

        return scope ? scope.querySelector('[data-role="'+name+'"]') : null;
    };

    Destination.prototype.getData = function(next)
    {
        var me = this;

        if (me.ajaxProgress)
            return;

        var ajaxUrl=me.ajaxUrl;
        if(this.contactid>0)
            ajaxUrl=ajaxUrl+'&contactid='+this.contactid;

        me.ajaxProgress = true;
        BX.ajax({
            method: 'POST',
            dataType: 'json',
            url: ajaxUrl,
            data: {
                sessid: BX.bitrix_sessid(),
                ajax_action: 'get_destination_data',
                type: me.type
            },
            onsuccess: function (response)
            {
                me.data = response.DATA || {};
                me.ajaxProgress = false;
                me.initDialog(next);
            }
        });
    };

    Destination.prototype.initDialog = function(next)
    {
        var me = this, data = this.data;

        if (!data)
        {
            me.getData(next);
            return;
        }

        var itemsSelected = {};
        for (var i = 0; i < me.selected.length; ++i)
        {
            itemsSelected[me.selected[i].id] = me.selected[i].entityType
        }

        var items = {}, itemsLast = {}, destSort =  data.DEST_SORT || {};

        if (this.type === 'responsible')
        {
            items = {
                users : data.USERS || {},
                department : data.DEPARTMENT || {},
                departmentRelation : data.DEPARTMENT_RELATION || {}
            };
            itemsLast =  {
                users: data.LAST.USERS || {}
            };

            if (!items["departmentRelation"])
            {
                items["departmentRelation"] = BX.SocNetLogDestination.buildDepartmentRelation(items["department"]);
            }
        }

        var isCrmFeed = false;
        var searchUrl = null;

        if (this.type === 'deal')
        {
            isCrmFeed = true;
            items = {
                deals : data.DEALS || {}
            };
            itemsLast =  {
                deals: data.LAST.DEALS || {},
                crm: []
            };
            searchUrl = DEFAULT_AJAX_URL + '&ajax_action=SEARCH_DESTINATION_DEALS';
        }

        if (this.type === 'order')
        {
            isCrmFeed = true;
            items = {
                orders : data.ORDERS || {}
            };
            itemsLast =  {
                orders: data.LAST.ORDERS || {},
                crm: []
            };
            searchUrl = DEFAULT_AJAX_URL + '&ajax_action=SEARCH_DESTINATION_ORDERS';
        }

        if (!me.inited)
        {
            me.inited = true;
            var destinationInput = me.getNode('destination-input');
            destinationInput.id = me.dialogId + 'input';

            var destinationInputBox = me.getNode('destination-input-box');
            destinationInputBox.id = me.dialogId + 'input-box';

            var tagNode = this.getNode('destination-tag');
            tagNode.id = this.dialogId + 'tag';

            var itemsNode = me.getNode('destination-items');

            if(this.contactid>0)
                searchUrl=searchUrl+'&contactid='+this.contactid;

            me.destinationObject = BX.SocNetLogDestination.init({
                pathToAjax: searchUrl,
                name : me.dialogId,
                searchInput : me.getNode('destination-input'),
                extranetUser :  false,
                bindMainPopup : {node: me.bindContainer, offsetTop: '5px', offsetLeft: '15px'},
                bindSearchPopup : {node: me.bindContainer, offsetTop : '5px', offsetLeft: '15px'},
                departmentSelectDisable: true,
                sendAjaxSearch: true,
                callback : {
                    select : function(item, type, search, bUndeleted)
                    {
                        me.addItem(item, type);
                        if (me.selectOne)
                            BX.SocNetLogDestination.closeDialog();
                    },
                    unSelect : BX.delegate(BX.SocNetLogDestination.BXfpUnSelectCallback, {
                        formName: me.dialogId,
                        inputContainerName: itemsNode,
                        inputName: destinationInput.id,
                        tagInputName: tagNode.id,
                        tagLink1: BX.message('CRM_ACTIVITY_PLANNER_DEST_1'),
                        tagLink2: BX.message('CRM_ACTIVITY_PLANNER_DEST_2')
                    }),
                    openDialog : BX.delegate(BX.SocNetLogDestination.BXfpOpenDialogCallback, {
                        inputBoxName: destinationInputBox.id,
                        inputName: destinationInput.id,
                        tagInputName: tagNode.id
                    }),
                    closeDialog : BX.delegate(BX.SocNetLogDestination.BXfpCloseDialogCallback, {
                        inputBoxName: destinationInputBox.id,
                        inputName: destinationInput.id,
                        tagInputName: tagNode.id
                    }),
                    openSearch : BX.delegate(BX.SocNetLogDestination.BXfpOpenDialogCallback, {
                        inputBoxName: destinationInputBox.id,
                        inputName: destinationInput.id,
                        tagInputName: tagNode.id
                    }),
                    closeSearch : BX.delegate(BX.SocNetLogDestination.BXfpCloseSearchCallback, {
                        inputBoxName: destinationInputBox.id,
                        inputName: destinationInput.id,
                        tagInputName: tagNode.id
                    })
                },
                items : items,
                itemsLast : itemsLast,
                itemsSelected : itemsSelected,
                isCrmFeed : isCrmFeed,
                useClientDatabase: false,
                destSort: destSort,
                allowAddUser: false
            });

            BX.bind(destinationInput, 'keyup', BX.delegate(BX.SocNetLogDestination.BXfpSearch, {
                formName: me.dialogId,
                inputName: destinationInput.id,
                tagInputName: tagNode.id
            }));
            BX.bind(destinationInput, 'keydown', BX.delegate(BX.SocNetLogDestination.BXfpSearchBefore, {
                formName: me.dialogId,
                inputName: destinationInput.id
            }));

            BX.SocNetLogDestination.BXfpSetLinkName({
                formName: me.dialogId,
                tagInputName: tagNode.id,
                tagLink1: BX.message('CRM_ACTIVITY_PLANNER_DEST_1'),
                tagLink2: BX.message('CRM_ACTIVITY_PLANNER_DEST_2')
            });
        }
        next();
    };

    Destination.prototype.addItem = function(item, type)
    {
        var me = this;
        var destinationInput = this.getNode('destination-input');
        var tagNode = this.getNode('destination-tag');
        var items = this.getNode('destination-items');
        var container = BX.clone(this.itemTpl);

        if (!BX.findChild(items, { attr : { 'data-id' : item.id }}, false, false))
        {
            if (me.selectOne && me.inited)
            {
                var toRemove = [];
                for (var i = 0; i < items.childNodes.length; ++i)
                {
                    toRemove.push({
                        itemId: items.childNodes[i].getAttribute('data-id'),
                        itemType: items.childNodes[i].getAttribute('data-type')
                    })
                }

                me.initDialog(function() {
                    for (var i = 0; i < toRemove.length; ++i)
                    {
                        BX.SocNetLogDestination.deleteItem(toRemove[i].itemId, toRemove[i].itemType, me.dialogId);
                    }
                });

                BX.cleanNode(items);
            }

            container.setAttribute('data-id', item.id);
            container.setAttribute('data-type', type);
            BX.addClass(container, container.getAttribute('data-class-prefix') + (me.type == 'responsible' ? 'users' : 'crm'));

            var containerText = this.getNode('text', container);
            var containerDelete = this.getNode('delete', container);
            var containerValue = this.getNode('value', container);

            containerText.innerHTML = BX.type.isString(item.name) ? BX.util.htmlspecialchars(item.name) : '';

            BX.bind(containerDelete, 'click', function(e) {
                if (me.selectOne && me.required)
                {
                    me.openDialog();
                }
                else
                {
                    me.initDialog(function() {
                        BX.SocNetLogDestination.deleteItem(item.id, type, me.dialogId);
                        BX.remove(container);
                    });
                }
                BX.PreventDefault(e);
            });

            BX.bind(containerDelete, 'mouseover', function(){
                BX.addClass(this.parentNode, this.getAttribute('data-hover-class'));
            });

            BX.bind(containerDelete, 'mouseout', function(){
                BX.removeClass(this.parentNode, this.getAttribute('data-hover-class'));
            });

            containerValue.name = me.valueInputName;
            containerValue.value = item.entityId;

            items.appendChild(container);

            if (!item.entityType)
                item.entityType = type;

            this.fireEvent('select', {item: item});
        }

        destinationInput.value = '';
        tagNode.innerHTML = BX.message('CRM_ACTIVITY_PLANNER_DEST_2');
    };

    Destination.prototype.addItems = function(items)
    {
        for(var i = 0; i < items.length; ++i)
        {
            this.addItem(items[i], items[i].entityType)
        }
    };

    Destination.prototype.openDialog = function(params)
    {
        var me = this;
        if(!this.disabled){
            this.initDialog(function()
            {
                BX.SocNetLogDestination.openDialog(me.dialogId, params);
            })
        }
    };
    Destination.prototype.fireEvent = function(eventName, params)
    {
        if (typeof this.events[eventName] === 'function')
        {
            this.events[eventName].call(this, params);
        }
    };
    Destination.prototype.onPlannerClose = function()
    {
        if (this.inited)
        {
            if (BX.SocNetLogDestination.isOpenDialog())
            {
                BX.SocNetLogDestination.closeDialog();
            }
            BX.SocNetLogDestination.closeSearch();
        }
    };

    Destination.prototype.setSelectCompany = function(selectcompany)
    {
        this.selectcompany=selectcompany;
        if(typeof (this.selectcompany)!="undefined"){
            this.setEnabled();
            if(this.selectcompany.entityType=="COMPANY"){
                var id=parseInt(this.selectcompany.entityId);
                if(id>0){
                    this.contactid=id;

                    var me = this;

                    if (me.ajaxProgress)
                        return;

                    var ajaxUrl=me.ajaxUrl;
                    if(this.contactid>0)
                        ajaxUrl=ajaxUrl+'&contactid='+this.contactid;

                    me.ajaxProgress = true;
                    BX.ajax({
                        method: 'POST',
                        dataType: 'json',
                        url: ajaxUrl,
                        data: {
                            sessid: BX.bitrix_sessid(),
                            ajax_action: 'get_destination_data',
                            type: me.type
                        },
                        onsuccess: function (response)
                        {
                            me.data = response.DATA || {};
                            me.ajaxProgress = false;
                        }
                    });

                    //this._communicationSearch._settings['serviceUrl']=this.ajaxUrl;
                    //this._communicationSearch._provider._settings['serviceUrl']=this.ajaxUrl;
                    //this._communicationSearch._provider._loadData();
                }
            }
        }else{
            this.setDisabled();
        }
    };

    Destination.prototype.setDisabled = function()
    {
        this.disabled=true;
        var container = this.getNode('template-destination-container');
        BX.addClass(container,'disabled');
    };

    Destination.prototype.setEnabled = function()
    {
        this.disabled=false;
        var container = this.getNode('template-destination-container');
        BX.removeClass(container,'disabled');
    };



    // <- Destination


// Communications ->
    var Communications = function(container, config)
    {
        this.id = 'crm-actpl-comm-' + ('' + new Date().getTime()).substr(6);
        this.items = [];

        var me = this;
        if (!config)
            config = {};

        this.bindContainer = container;
        this.ajaxUrl = config.ajaxUrl || COMMUNICATIONS_AJAX_URL;
        this.itemTpl = config.itemTpl;

        this.selectOne = config.selectOne || false;
        this.selectCallback = config.selectCallback || false;

        this.bindContainer.appendChild(BX.clone(config.containerTpl));
        var tagNode = this.getNode('destination-tag');

        BX.bind(tagNode, 'focus', function(e) {
            me.openDialog();
            return BX.PreventDefault(e);
        });
        BX.bind(this.bindContainer, 'click', function(e) {
            me.openDialog();
            return BX.PreventDefault(e);
        });

        var communicationType = BX.CrmCommunicationType.undefined;
        if (config.communicationType === 'PHONE')
            communicationType = BX.CrmCommunicationType.phone;
        if (config.communicationType === 'EMAIL')
            communicationType = BX.CrmCommunicationType.email;

        if(typeof(BX.CrmCommunicationSearch.messages) === 'undefined')
        {
            BX.CrmCommunicationSearch.messages =
                {
                    SearchTab: BX.message('CRM_ACTIVITY_PLANNER_COMMUNICATION_SEARCH_TAB'),
                    NoData: BX.message('CRM_ACTIVITY_PLANNER_COMMUNICATION_SEARCH_NO_DATA')
                }
        }

        this._communicationSearch = BX.CrmCommunicationSearch.create(this.id, {
            entityType : config.entityType,
            entityId: config.entityId,
            serviceUrl: me.ajaxUrl,
            communicationType:  communicationType,
            selectCallback: BX.delegate(this.selectCommunication, this),
            enableSearch: true,
            enableDataLoading: true,
            dialogAutoHide: true
        });

        if (communicationType === BX.CrmCommunicationType.phone)
        {
            var input = this.getNode('destination-input');
            BX.bind(input, 'keypress', BX.delegate(this.inputKeypress, this));
        }

        this.addItems(config.selected ? BX.clone(config.selected) : []);
    };
    Communications.prototype.inputKeypress = function(e)
    {
        if(!e)
            e = window.event;

        if(e.keyCode !== 13)
            return;

        var input = this.getNode('destination-input');

        if(BX.type.isNotEmptyString(input.value))
        {
            var rx = /^\s*\+?[\d-\s\(\)]+\s*$/;
            if (rx.test(input.value))
            {
                this.addItem(
                    {
                        entityId: '0',
                        entityTitle: '',
                        entityType: 'CONTACT',
                        type: 'PHONE',
                        value: input.value
                    },
                    true
                );
            }
        }
    };

    Communications.prototype.getNode = function(name, scope)
    {
        if (!scope)
            scope = this.bindContainer;

        return scope ? scope.querySelector('[data-role="'+name+'"]') : null;
    };

    Communications.prototype.selectCommunication = function(communication)
    {
        if(this.selectCallback)
            this.selectCallback(communication);
        this.addItem(communication.getSettings(), true);
    };

    Communications.prototype.addItem = function(item, closeDialog)
    {
        if (item.type === null)
            item.type = '';

        if (item.type === '' && item.value === null)
            item.value = '';

        item.entityId = parseInt(item.entityId);

        for(var i = 0; i < this.items.length; ++i)
        {
            if (
                this.items[i].type === item.type
                && this.items[i].value === item.value
                && this.items[i].entityId === item.entityId
                && this.items[i].entityType === item.entityType
            )
                return;
        }

        var me = this, itemsNode = this.getNode('destination-items');

        if (this.selectOne)
        {
            this.items = [];
            BX.cleanNode(itemsNode);
        }

        this.items.push(item);

        var container = BX.clone(this.itemTpl);
        BX.addClass(container, container.getAttribute('data-class-prefix') + 'crm');

        var containerText = this.getNode('text', container);
        var containerDelete = this.getNode('delete', container);

        containerText.innerHTML = [
            BX.type.isString(item.entityTitle) ? BX.util.htmlspecialchars(item.entityTitle) : '',
            BX.type.isString(item.value) ? BX.util.htmlspecialchars(item.value) : ''
        ].join(' ');

        BX.bind(containerDelete, 'click', function(e) {
            me.deleteItem(item);
            BX.remove(container);
            BX.PreventDefault(e)
        });

        BX.bind(containerDelete, 'mouseover', function(){
            BX.addClass(this.parentNode, this.getAttribute('data-hover-class'));
        });

        BX.bind(containerDelete, 'mouseout', function(){
            BX.removeClass(this.parentNode, this.getAttribute('data-hover-class'));
        });

        itemsNode.appendChild(container);

        var tagNode = this.getNode('destination-tag');
        tagNode.innerHTML = BX.message('CRM_ACTIVITY_PLANNER_DEST_2');
        if (closeDialog)
            this._communicationSearch.closeDialog();
    };

    Communications.prototype.addItems = function(items)
    {
        for(var i = 0; i < items.length; ++i)
        {
            this.addItem(items[i], items[i].entityType)
        }
        var tagNode = this.getNode('destination-tag');

        tagNode.innerHTML = (
            items.length <= 0
                ? BX.message('CRM_ACTIVITY_PLANNER_DEST_1')
                : BX.message('CRM_ACTIVITY_PLANNER_DEST_2')
        );
    };

    Communications.prototype.deleteItem = function(item)
    {
        for(var i = 0; i < this.items.length; ++i)
        {
            if (this.items[i] === item)
                this.items.splice(i, 1);
        }
        return this;
    };

    Communications.prototype.openDialog = function()
    {
        var inputBox = this.getNode('destination-input-box');
        var input = this.getNode('destination-input');
        var tagNode = this.getNode('destination-tag');

        BX.style(inputBox, 'display', 'inline-block');
        BX.style(tagNode, 'display', 'none');

        if(!this._communicationSearchController)
        {
            this._communicationSearchController = BX.CrmCommunicationSearchController.create(this._communicationSearch, input);
            this._communicationSearchController.start();
        }
        this._communicationSearch.openDialog(this.bindContainer,
            BX.delegate(this.closeDialog, this),
            {zIndex: 999}
        );

        BX.defer(BX.focus)(input);
    };
    Communications.prototype.closeDialog = function()
    {
        var inputBox = this.getNode('destination-input-box');
        var input = this.getNode('destination-input');
        var tagNode = this.getNode('destination-tag');

        if(this._communicationSearchController)
        {
            this._communicationSearchController.stop();
            this._communicationSearchController = null;
        }

        BX.style(tagNode, 'display', 'inline-block');
        BX.style(inputBox, 'display', 'none');
        input.value = '';
    };

    Communications.prototype.onPlannerClose = function()
    {
        this._communicationSearch.closeDialog();
    };
    // <- Communications






    // Contacts ->
    var Contacts = function(container, config)
    {
        this.id = 'crm-actpl-comm2-' + ('' + new Date().getTime()).substr(6);
        this.items = [];

        this.disabled=false;

        var me = this;
        if (!config)
            config = {};

        this.bindContainer = container;
        this.ajaxUrl = COMMUNICATIONS_AJAX_URL_CONTACT || COMMUNICATIONS_AJAX_URL_CONTACT;
        this.itemTpl = config.itemTpl;

        this.selectcompany = config.selectcompany || false;;

        this.selectOne = config.selectOne || false;

        this.bindContainer.appendChild(BX.clone(config.containerTpl));
        var tagNode = this.getNode('destination-tag');

        BX.bind(tagNode, 'focus', function(e) {
            me.openDialog();
            return BX.PreventDefault(e);
        });
        BX.bind(this.bindContainer, 'click', function(e) {
            me.openDialog();
            return BX.PreventDefault(e);
        });

        var communicationType = BX.CrmCommunicationType.undefined;
        if (config.communicationType === 'PHONE')
            communicationType = BX.CrmCommunicationType.phone;
        if (config.communicationType === 'EMAIL')
            communicationType = BX.CrmCommunicationType.email;

        if(typeof(BX.CrmCommunicationSearch.messages) === 'undefined')
        {
            BX.CrmCommunicationSearch.messages =
                {
                    SearchTab: BX.message('CRM_ACTIVITY_PLANNER_COMMUNICATION_SEARCH_TAB'),
                    NoData: BX.message('CRM_ACTIVITY_PLANNER_COMMUNICATION_SEARCH_NO_DATA')
                }
        }

        this._communicationSearch = BX.CrmCommunicationSearch.create(this.id, {
            entityType : config.entityType,
            entityId: config.entityId,
            serviceUrl: me.ajaxUrl,
            communicationType:  communicationType,
            selectCallback: BX.delegate(this.selectCommunication, this),
            enableSearch: true,
            enableDataLoading: true,
            dialogAutoHide: true
        });

        if (communicationType === BX.CrmCommunicationType.phone)
        {
            var input = this.getNode('destination-input');
            BX.bind(input, 'keypress', BX.delegate(this.inputKeypress, this));
        }

        this.addItems(config.selected ? BX.clone(config.selected) : []);
    };
    Contacts.prototype.inputKeypress = function(e)
    {
        if(!e)
            e = window.event;

        if(e.keyCode !== 13)
            return;

        var input = this.getNode('destination-input');

        if(BX.type.isNotEmptyString(input.value))
        {
            var rx = /^\s*\+?[\d-\s\(\)]+\s*$/;
            if (rx.test(input.value))
            {
                this.addItem(
                    {
                        entityId: '0',
                        entityTitle: '',
                        entityType: 'CONTACT',
                        type: 'PHONE',
                        value: input.value
                    },
                    true
                );
            }
        }
    };

    Contacts.prototype.getNode = function(name, scope)
    {
        if (!scope)
            scope = this.bindContainer;

        return scope ? scope.querySelector('[data-role="'+name+'"]') : null;
    };

    Contacts.prototype.selectCommunication = function(communication)
    {
        this.addItem(communication.getSettings(), true);
    };

    Contacts.prototype.setSelectCompany = function(selectcompany)
    {
        this.selectcompany=selectcompany;

        if(typeof (this.selectcompany)!="undefined"){
            this.setEnabled();
            if(this.selectcompany.entityType=="COMPANY"){
                var id=parseInt(this.selectcompany.entityId);
                if(id>0){
                    this.ajaxUrl=COMMUNICATIONS_AJAX_URL_CONTACT+"&contactid="+id;
                    this._communicationSearch._settings['serviceUrl']=this.ajaxUrl;
                    this._communicationSearch._provider._settings['serviceUrl']=this.ajaxUrl;

                    if (parseInt(this._communicationSearch._provider._entityId)==0)
                        this._communicationSearch._provider._entityId=1;

                    if (this._communicationSearch._provider._entityType=="")
                        this._communicationSearch._provider._entityType="DEAL";

                    this._communicationSearch._provider._loadData();
                }
            }
        }else{
            this.setDisabled();
        }
    };

    /*Contacts.prototype._loadData=function(_entityType,_entityId,serviceUrl)
    {
        var serviceUrl = this.getSetting("serviceUrl", "");

        if(this._entityType === "" || this._entityId === 0 || serviceUrl === "")
        {
            return;
        }

        BX.ajax(
            {
                "url": serviceUrl,
                "method": "POST",
                "dataType": "json",
                "data":
                    {
                        "ACTION" : "GET_ENTITY_COMMUNICATIONS",
                        "ENTITY_TYPE": this._entityType,
                        "ENTITY_ID": this._entityId,
                        "COMMUNICATION_TYPE": this._commType
                    },
                "async": false,
                "start": true,
                "onsuccess": BX.delegate(this._handleRequestCompletion, this),
                "onfailure": BX.delegate(this._handleRequestError, this)
            }
        );
    }*/

    Contacts.prototype.addItem = function(item, closeDialog)
    {

        if (item.type === null)
            item.type = '';

        if (item.type === '' && item.value === null)
            item.value = '';

        item.entityId = parseInt(item.entityId);

        for(var i = 0; i < this.items.length; ++i)
        {
            if (
                this.items[i].type === item.type
                && this.items[i].value === item.value
                && this.items[i].entityId === item.entityId
                && this.items[i].entityType === item.entityType
            )
                return;
        }

        var me = this, itemsNode = this.getNode('destination-items');

        if (this.selectOne)
        {
            this.items = [];
            BX.cleanNode(itemsNode);
        }

        this.items.push(item);

        var container = BX.clone(this.itemTpl);
        BX.addClass(container, container.getAttribute('data-class-prefix') + 'crm');

        var containerText = this.getNode('text', container);
        var containerDelete = this.getNode('delete', container);

        containerText.innerHTML = [
            BX.type.isString(item.entityTitle) ? BX.util.htmlspecialchars(item.entityTitle) : '',
            BX.type.isString(item.value) ? BX.util.htmlspecialchars(item.value) : ''
        ].join(' ');

        BX.bind(containerDelete, 'click', function(e) {
            me.deleteItem(item);
            BX.remove(container);
            BX.PreventDefault(e)
        });

        BX.bind(containerDelete, 'mouseover', function(){
            BX.addClass(this.parentNode, this.getAttribute('data-hover-class'));
        });

        BX.bind(containerDelete, 'mouseout', function(){
            BX.removeClass(this.parentNode, this.getAttribute('data-hover-class'));
        });

        itemsNode.appendChild(container);

        var tagNode = this.getNode('destination-tag');
        tagNode.innerHTML = BX.message('CRM_ACTIVITY_PLANNER_DEST_2');
        if (closeDialog)
            this._communicationSearch.closeDialog();
    };

    Contacts.prototype.addItems = function(items)
    {
        for(var i = 0; i < items.length; ++i)
        {
            this.addItem(items[i], items[i].entityType)
        }
        var tagNode = this.getNode('destination-tag');

        tagNode.innerHTML = (
            items.length <= 0
                ? BX.message('CRM_ACTIVITY_PLANNER_DEST_1')
                : BX.message('CRM_ACTIVITY_PLANNER_DEST_2')
        );
    };

    Contacts.prototype.deleteItem = function(item)
    {
        for(var i = 0; i < this.items.length; ++i)
        {
            if (this.items[i] === item)
                this.items.splice(i, 1);
        }
        return this;
    };

    Contacts.prototype.openDialog = function()
    {
        if(!this.disabled){
            var inputBox = this.getNode('destination-input-box');
            var input = this.getNode('destination-input');
            var tagNode = this.getNode('destination-tag');

            BX.style(inputBox, 'display', 'inline-block');
            BX.style(tagNode, 'display', 'none');

            if(!this._communicationSearchController)
            {
                this._communicationSearchController = BX.CrmCommunicationSearchController.create(this._communicationSearch, input);
                this._communicationSearchController.start();
            }
            this._communicationSearch.openDialog(this.bindContainer,
                BX.delegate(this.closeDialog, this),
                {zIndex: 999}
            );

            BX.defer(BX.focus)(input);
        }

    };
    Contacts.prototype.closeDialog = function()
    {
        var inputBox = this.getNode('destination-input-box');
        var input = this.getNode('destination-input');
        var tagNode = this.getNode('destination-tag');

        if(this._communicationSearchController)
        {
            this._communicationSearchController.stop();
            this._communicationSearchController = null;
        }

        BX.style(tagNode, 'display', 'inline-block');
        BX.style(inputBox, 'display', 'none');
        input.value = '';
    };

    Contacts.prototype.setDisabled = function()
    {
        this.disabled=true;
        var container = this.getNode('template-destination-container');
        BX.addClass(container,'disabled');
    };

    Contacts.prototype.setEnabled = function()
    {
        this.disabled=false;
        var container = this.getNode('template-destination-container');
        BX.removeClass(container,'disabled');
    };

    Contacts.prototype.onPlannerClose = function()
    {
        this._communicationSearch.closeDialog();
    };
    // <- Contacts



    // Stores  ->
    var Stores = function(container, config)
    {
        this.id = 'crm-actpl-comm2-' + ('' + new Date().getTime()).substr(6);
        this.items = [];

        this.disabled=false;

        var me = this;
        if (!config)
            config = {};

        this.bindContainer = container;
        this.ajaxUrl = COMMUNICATIONS_AJAX_URL_STORE;
        this.itemTpl = config.itemTpl;

        this.selectcompany = config.selectcompany || false;;

        this.selectOne = config.selectOne || false;

        this.bindContainer.appendChild(BX.clone(config.containerTpl));
        var tagNode = this.getNode('destination-tag');

        BX.bind(tagNode, 'focus', function(e) {
            me.openDialog();
            return BX.PreventDefault(e);
        });
        BX.bind(this.bindContainer, 'click', function(e) {
            me.openDialog();
            return BX.PreventDefault(e);
        });

        var communicationType = BX.CrmCommunicationType.undefined;
        if (config.communicationType === 'PHONE')
            communicationType = BX.CrmCommunicationType.phone;
        if (config.communicationType === 'EMAIL')
            communicationType = BX.CrmCommunicationType.email;

        if(typeof(BX.CrmCommunicationSearch.messages) === 'undefined')
        {
            BX.CrmCommunicationSearch.messages =
                {
                    SearchTab: BX.message('CRM_ACTIVITY_PLANNER_COMMUNICATION_SEARCH_TAB'),
                    NoData: BX.message('CRM_ACTIVITY_PLANNER_COMMUNICATION_SEARCH_NO_DATA')
                }
        }

        this._communicationSearch = BX.CrmCommunicationSearch.create(this.id, {
            //entityType : config.entityType,
            entityId: config.entityId,
            serviceUrl: me.ajaxUrl,
            communicationType:  communicationType,
            selectCallback: BX.delegate(this.selectCommunication, this),
            enableSearch: true,
            enableDataLoading: true,
            dialogAutoHide: true
        });

        if (communicationType === BX.CrmCommunicationType.phone)
        {
            var input = this.getNode('destination-input');
            BX.bind(input, 'keypress', BX.delegate(this.inputKeypress, this));
        }

        this.addItems(config.selected ? BX.clone(config.selected) : []);
    };
    Stores.prototype.inputKeypress = function(e)
    {
        if(!e)
            e = window.event;

        if(e.keyCode !== 13)
            return;

        var input = this.getNode('destination-input');

        if(BX.type.isNotEmptyString(input.value))
        {
            var rx = /^\s*\+?[\d-\s\(\)]+\s*$/;
            if (rx.test(input.value))
            {
                this.addItem(
                    {
                        entityId: '0',
                        entityTitle: '',
                        entityType: 'CONTACT',
                        type: 'PHONE',
                        value: input.value
                    },
                    true
                );
            }
        }
    };

    Stores.prototype.getNode = function(name, scope)
    {
        if (!scope)
            scope = this.bindContainer;

        return scope ? scope.querySelector('[data-role="'+name+'"]') : null;
    };

    Stores.prototype.selectCommunication = function(communication)
    {
        this.addItem(communication.getSettings(), true);
    };

    Stores.prototype.setSelectCompany = function(selectcompany)
    {
        this.selectcompany=selectcompany;

        if(typeof (this.selectcompany)!="undefined"){
            this.setEnabled();
            if(this.selectcompany.entityType=="COMPANY"){
                var id=parseInt(this.selectcompany.entityId);
                if(id>0){
                    this.ajaxUrl=COMMUNICATIONS_AJAX_URL_STORE+"&contactid="+id;
                    this._communicationSearch._settings['serviceUrl']=this.ajaxUrl;
                    this._communicationSearch._provider._settings['serviceUrl']=this.ajaxUrl;

                    if (parseInt(this._communicationSearch._provider._entityId)==0)
                        this._communicationSearch._provider._entityId=1;

                    if (this._communicationSearch._provider._entityType=="")
                        this._communicationSearch._provider._entityType="DEAL";

                    this._communicationSearch._provider._loadData();
                }
            }
        }else{
            this.setDisabled();
        }
    };

    /*Contacts.prototype._loadData=function(_entityType,_entityId,serviceUrl)
    {
        var serviceUrl = this.getSetting("serviceUrl", "");

        if(this._entityType === "" || this._entityId === 0 || serviceUrl === "")
        {
            return;
        }

        BX.ajax(
            {
                "url": serviceUrl,
                "method": "POST",
                "dataType": "json",
                "data":
                    {
                        "ACTION" : "GET_ENTITY_COMMUNICATIONS",
                        "ENTITY_TYPE": this._entityType,
                        "ENTITY_ID": this._entityId,
                        "COMMUNICATION_TYPE": this._commType
                    },
                "async": false,
                "start": true,
                "onsuccess": BX.delegate(this._handleRequestCompletion, this),
                "onfailure": BX.delegate(this._handleRequestError, this)
            }
        );
    }*/

    Stores.prototype.addItem = function(item, closeDialog)
    {

        if (item.type === null)
            item.type = '';

        if (item.type === '' && item.value === null)
            item.value = '';

        item.entityId = parseInt(item.entityId);

        for(var i = 0; i < this.items.length; ++i)
        {
            if (
                this.items[i].type === item.type
                && this.items[i].value === item.value
                && this.items[i].entityId === item.entityId
                && this.items[i].entityType === item.entityType
            )
                return;
        }

        var me = this, itemsNode = this.getNode('destination-items');

        if (this.selectOne)
        {
            this.items = [];
            BX.cleanNode(itemsNode);
        }

        this.items.push(item);

        var container = BX.clone(this.itemTpl);
        BX.addClass(container, container.getAttribute('data-class-prefix') + 'crm');

        var containerText = this.getNode('text', container);
        var containerDelete = this.getNode('delete', container);

        containerText.innerHTML = [
            BX.type.isString(item.entityTitle) ? BX.util.htmlspecialchars(item.entityTitle) : '',
            BX.type.isString(item.value) ? BX.util.htmlspecialchars(item.value) : ''
        ].join(' ');

        BX.bind(containerDelete, 'click', function(e) {
            me.deleteItem(item);
            BX.remove(container);
            BX.PreventDefault(e)
        });

        BX.bind(containerDelete, 'mouseover', function(){
            BX.addClass(this.parentNode, this.getAttribute('data-hover-class'));
        });

        BX.bind(containerDelete, 'mouseout', function(){
            BX.removeClass(this.parentNode, this.getAttribute('data-hover-class'));
        });

        itemsNode.appendChild(container);

        var tagNode = this.getNode('destination-tag');
        tagNode.innerHTML = BX.message('CRM_ACTIVITY_PLANNER_DEST_2');
        if (closeDialog)
            this._communicationSearch.closeDialog();
    };

    Stores.prototype.addItems = function(items)
    {
        for(var i = 0; i < items.length; ++i)
        {
            this.addItem(items[i], items[i].entityType)
        }
        var tagNode = this.getNode('destination-tag');

        tagNode.innerHTML = (
            items.length <= 0
                ? BX.message('CRM_ACTIVITY_PLANNER_DEST_1')
                : BX.message('CRM_ACTIVITY_PLANNER_DEST_2')
        );
    };

    Stores.prototype.deleteItem = function(item)
    {
        for(var i = 0; i < this.items.length; ++i)
        {
            if (this.items[i] === item)
                this.items.splice(i, 1);
        }
        return this;
    };

    Stores.prototype.openDialog = function()
    {
        if(!this.disabled){
            var inputBox = this.getNode('destination-input-box');
            var input = this.getNode('destination-input');
            var tagNode = this.getNode('destination-tag');

            BX.style(inputBox, 'display', 'inline-block');
            BX.style(tagNode, 'display', 'none');

            if(!this._communicationSearchController)
            {
                this._communicationSearchController = BX.CrmCommunicationSearchController.create(this._communicationSearch, input);
                this._communicationSearchController.start();
            }
            this._communicationSearch.openDialog(this.bindContainer,
                BX.delegate(this.closeDialog, this),
                {zIndex: 999}
            );

            BX.defer(BX.focus)(input);
        }

    };
    Stores.prototype.closeDialog = function()
    {
        var inputBox = this.getNode('destination-input-box');
        var input = this.getNode('destination-input');
        var tagNode = this.getNode('destination-tag');

        if(this._communicationSearchController)
        {
            this._communicationSearchController.stop();
            this._communicationSearchController = null;
        }

        BX.style(tagNode, 'display', 'inline-block');
        BX.style(inputBox, 'display', 'none');
        input.value = '';
    };

    Stores.prototype.setDisabled = function()
    {
        this.disabled=true;
        var container = this.getNode('template-destination-container');
        BX.addClass(container,'disabled');
    };

    Stores.prototype.setEnabled = function()
    {
        this.disabled=false;
        var container = this.getNode('template-destination-container');
        BX.removeClass(container,'disabled');
    };

    Stores.prototype.onPlannerClose = function()
    {
        this._communicationSearch.closeDialog();
    };
    // <- Contacts












});
