/**
 * @author Sergei Vizel <sergei.vizel@modera.org>
 */
Ext.define('Modera.backend.security.toolscontribution.view.user.NewWindow', {
    extend: 'MFC.window.NewAndEditRecordWindow',
    alias: 'widget.modera-backend-security-user-newwindow',

    // l10n
    newRecordTitleText: 'Create new user',
    placeHolderText: 'Type here',
    personalIdLabelText: 'Personal ID',
    firstNameLabelText: 'First name',
    lastNameLabelText: 'Last name',
    usernameLabelText: 'Principal',
    emailLabelText: 'Email',
    sendPasswordText: 'Generate and send password to provided e-mail',

    // override
    constructor: function(config) {
        var me = this;

        var defaults = {
            tid: 'newUserWindow',
            type: 'new',
            groupName: 'main-form',
            resizable: false,
            autoScroll: true,
            width: 520,
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
                items: [
                    {
                        xtype: 'hiddenfield',
                        name: 'id'
                    },
                    {
                        name: 'firstName',
                        msgTarget: 'under',
                        fieldLabel: me.firstNameLabelText,
                        emptyText: me.placeHolderText,
                        tid: 'firstNameField'
                    },
                    {
                        name: 'lastName',
                        msgTarget: 'under',
                        fieldLabel: me.lastNameLabelText,
                        emptyText: me.placeHolderText,
                        tid: 'lastNameField'
                    },
                    {
                        name: 'personalId',
                        msgTarget: 'under',
                        fieldLabel: me.personalIdLabelText,
                        emptyText: me.placeHolderText,
                        tid: 'personalIdField'
                    },
                    {
                        name: 'username',
                        msgTarget: 'under',
                        fieldLabel: me.usernameLabelText,
                        emptyText: me.placeHolderText,
                        tid: 'usernameField'
                    },
                    {
                        itemId: 'email',
                        name: 'email',
                        vtype: 'email',
                        msgTarget: 'under',
                        fieldLabel: me.emailLabelText,
                        emptyText: me.placeHolderText,
                        tid: 'emailField'
                    },
                    {
                        itemId: 'sendPassword',
                        xtype: 'checkbox',
                        name: 'sendPassword',
                        fieldLabel: '&nbsp;',
                        labelSeparator: '',
                        boxLabel: me.sendPasswordText,
                        allowBlank: true,
                        disabled: true
                    }
                ]
            }
        };

        this.config = Ext.apply(defaults, config || {});
        this.callParent([this.config]);

        this.assignListeners();
    },

    // private
    assignListeners: function() {
        var me = this;
        var validate = function(field) {
            var checkbox = me.down('#sendPassword');

            if ((field.getValue() != '') && field.isValid()) {
                checkbox.setDisabled(false);
            } else {
                checkbox.setDisabled(true);
                checkbox.setValue(false);
            }
        };
        me.down('#email').on('change', validate);
        me.down('#email').on('validitychange', validate);
    },

    loadData: function(data) {
        var me = this;
        me.down('form').getForm().setValues(data);
    }
});