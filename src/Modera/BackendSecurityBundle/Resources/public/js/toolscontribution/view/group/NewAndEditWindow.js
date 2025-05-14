/**
 * @copyright 2014 Modera Foundation
 */
Ext.define('Modera.backend.security.toolscontribution.view.group.NewAndEditWindow', {
    extend: 'MFC.window.NewAndEditRecordWindow',
    alias: 'widget.modera-backend-security-group-newwindow',

    requires: [
    ],

    // l10n
    placeHolderText: 'Type here',
    nameFieldText: 'Name',
    refNameFieldText: 'Reference name',
    refNameLabelText: 'Reference name',
    groupNameLabelText: 'Group Name',

    // override
    constructor: function(config) {
        var me = this;

        var defaults = {
            tid: 'newAndEditGroupWindow',
            resizable: false,
            autoScroll: true,
            width: 500,
            maxHeight: Ext.getBody().getViewSize().height - 60,
            items: {
                xtype: 'form',
                groupName: 'main-form',
                // see MFC.GroupedDataLoader
                loadData: function(data) {
                    this.getForm().setValues(data);
                },
                defaultType: 'textfield',
                layout: {
                    type: 'vbox',
                    align: 'stretch'
                },
                defaults: {
                    labelAlign: 'right'
                },
                items: [
                    {
                        xtype: 'hiddenfield',
                        name: 'id'
                    },
                    {
                        name: 'name',
                        labelWidth: 120,
                        fieldLabel: me.groupNameLabelText,
                        emptyText: me.nameFieldText,
                        tid: 'groupNameField'
                    },
                    {
                        name: 'refName',
                        labelWidth: 120,
                        fieldLabel: me.refNameLabelText,
                        emptyText:  me.refNameFieldText
                    }
                ]
            }
        };

        this.config = Ext.apply(defaults, config || {});
        this.callParent([this.config]);
    }
});