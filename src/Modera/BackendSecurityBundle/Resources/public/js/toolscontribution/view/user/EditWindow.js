/**
 * @author Sergei Vizel <sergei.vizel@modera.org>
 */
Ext.define('Modera.backend.security.toolscontribution.view.user.EditWindow', {
    extend: 'MFC.window.NewAndEditRecordWindow',
    alias: 'widget.modera-backend-security-user-editwindow',
    tid: 'securityUserEditWindow',

    // l10n
    editRecordTitleText: 'Edit user',
    placeHolderText: 'Type here',
    personalIdLabelText: 'Personal ID',
    firstNameLabelText: 'First name',
    lastNameLabelText: 'Last name',
    usernameLabelText: 'Principal',
    emailLabelText: 'Email',
    userIdLabelText: 'User Id',

    // override
    constructor: function(config) {
        var me = this;

        var items = [
            {
                xtype: 'hiddenfield',
                name: 'id'
            },
            {
                xtype: 'displayfield',
                tid: 'displayIdField',
                fieldLabel: me.userIdLabelText,
                name: 'displayId'
            },
            {
                name: 'firstName',
                tid: 'firstNameField',
                fieldLabel: me.firstNameLabelText,
                emptyText: me.placeHolderText
            },
            {
                name: 'lastName',
                tid: 'lastNameField',
                fieldLabel: me.lastNameLabelText,
                emptyText: me.placeHolderText
            },
            {
                name: 'personalId',
                tid: 'personalIdField',
                fieldLabel: me.personalIdLabelText,
                emptyText: me.placeHolderText
            }
        ];

        if (!config.onlyProfileInformation) {
            items.push({
                name: 'username',
                tid: 'usernameField',
                fieldLabel: me.usernameLabelText,
                emptyText: me.placeHolderText
            });
        }

        items.push({
            name: 'email',
            tid: 'emailField',
            fieldLabel: me.emailLabelText,
            emptyText: me.placeHolderText
        });

        var defaults = {
            type: 'edit',
            groupName: 'main-form',
            resizable: false,
            autoScroll: true,
            width: 500,
            maxHeight: Ext.getBody().getViewSize().height - 60,
            items: {
                xtype: 'form',
                defaultType: 'textfield',
                layout: {
                    type: 'vbox',
                    align: 'stretch'
                },
                defaults: {
                    labelAlign: 'right'
                },
                items: items
            }
        };

        this.config = Ext.apply(defaults, config || {});
        this.callParent([this.config]);
    },

    loadData: function(data) {
        // we have to introduce a separate field because ExtJs doesn't properly read values of displayfield-s
        data['displayId'] = data['id'];
        var me = this;
        me.down('form').getForm().setValues(data);

        var meta = data['meta'];
        if (meta['modera_security'] || false) {
            if (meta['modera_security']['read_only_fields'] || false) {
                Ext.Array.each(meta['modera_security']['read_only_fields'], function(fieldName) {
                    var field = me.down('form').getForm().findField(fieldName);
                    if (field) {
                        field.setDisabled(true);
                    }
                });
            }
        }
    }
});