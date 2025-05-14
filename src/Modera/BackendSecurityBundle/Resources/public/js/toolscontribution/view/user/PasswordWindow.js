/**
 * @copyright 2014 Modera Foundation
 */
Ext.define('Modera.backend.security.toolscontribution.view.user.PasswordWindow', {
    extend: 'MFC.window.NewAndEditRecordWindow',
    alias: 'widget.modera-backend-security-user-passwordwindow',

    requires: [
        'MFC.container.Header',
        'MFC.container.Message'
    ],

    // l10n
    recordTitleText: 'Change password for "{0}"',
    recordNewTitleText: 'Create password for user',
    placeHolderText: 'Type here',
    passwordLabelText: 'Password',
    repeatPasswordLabelText: '... again',
    generatePasswordBtnText: 'Generate',
    wrongPasswordText: 'Passwords must be equal',
    sendPasswordText: 'Send password to user`s e-mail',
    passwordRotationNeededText: 'It has been a while since you have changed your password last time, for keeping your account secure please change it now.',

    newUser: false,
    passwordRotation: false,
    hideSendPassword: false,
    hideGeneratePassword: false,

    // override
    constructor: function(config) {
        var me = this;

        if (config && config.newUser) {
            me.newUser = config.newUser;
        }
        if (config && config.passwordRotation) {
            me.passwordRotation = config.passwordRotation;
        }
        if (config && config.hideSendPassword) {
            me.hideSendPassword = config.hideSendPassword;
        }
        if (config && config.hideGeneratePassword) {
            me.hideGeneratePassword = config.hideGeneratePassword;
        }

        var defaults = {
            tid: 'changePasswordWindow',
            title: (me.newUser)? me.recordNewTitle : me.recordTitleText,
            hideCloseButton: me.passwordRotation,
            groupName: 'compact-list',
            resizable: false,
            autoScroll: true,
            width: 500,
            bodyPadding: '0 10 10 10',
            maxHeight: Ext.getBody().getViewSize().height - 60,
            actions: [
                '->',
                {
                    itemId: 'saveBtn',
                    disabled: true,
                    text: this.saveAndCloseBtnText,
                    iconCls: 'mfc-icon-apply-24',
                    scale: 'medium',
                    handler: function() {
                        if (false !== me.fireEvent('beforesaveandclose', me)) {
                            me.fireEvent('saveandclose', me);
                        }
                    },
                    tid: 'saveAndCloseBtn'
                }
            ],
            items: {
                xtype: 'form',
                layout: {
                    type: 'vbox',
                    align: 'stretch'
                },
                items: [
                    {
                        hidden: !me.passwordRotation,
                        xtype: 'mfc-header',
                        type: 'notification',
                        defaults: {
                            xtype: 'mfc-message',
                            layout: {
                                type: 'hbox',
                                pack: 'center',
                                align: 'top'
                            }
                        },
                        items: [
                            {
                                type: 'warning',
                                text: {
                                    width: 425,
                                    html: this.passwordRotationNeededText
                                }
                            }
                        ]
                    },
                    {
                        xtype: 'hiddenfield',
                        name: 'id'
                    },
                    {
                        xtype: 'fieldcontainer',
                        margin: '10 0 0 0',
                        layout: 'hbox',
                        items: [
                            {
                                xtype: 'fieldcontainer',
                                layout: 'anchor',
                                defaultType: 'textfield',
                                defaults: {
                                    anchor: '0',
                                    labelAlign: 'right',
                                    allowBlank: false,
                                    enableKeyEvents: true
                                },
                                items: [
                                    {
                                        name: 'plainPassword',
                                        fieldLabel: me.passwordLabelText,
                                        emptyText: me.placeHolderText,
                                        inputType: 'password',
                                        tid: 'passwordField',
                                        msgTarget: 'under',
                                        validateOnBlur: false,
                                        listeners: {
                                            blur: function (field) {
                                                setTimeout(function () {
                                                    if (!field.hasFocus) {
                                                        field.validate();
                                                    }
                                                }, 200);
                                            }
                                        }
                                    },
                                    {
                                        name: '_plainPassword',
                                        fieldLabel: me.repeatPasswordLabelText,
                                        emptyText: me.placeHolderText,
                                        inputType: 'password',
                                        tid: 'passwordAgainField',
                                        msgTarget: 'under',
                                        validateOnBlur: false,
                                        submitValue: false,
                                        listeners: {
                                            blur: function (field) {
                                                setTimeout(function () {
                                                    if (!field.hasFocus) {
                                                        field.validate();
                                                    }
                                                }, 200);
                                            }
                                        }
                                    },
                                    {
                                        xtype: 'checkbox',
                                        name: 'sendPassword',
                                        fieldLabel: '&nbsp;',
                                        labelSeparator: '',
                                        hidden: me.hideSendPassword || me.newUser,
                                        boxLabel: me.sendPasswordText,
                                        allowBlank: true,
                                        disabled: false
                                    }
                                ],
                                flex: 1
                            },
                            {
                                itemId: 'generatePassword',
                                xtype: 'button',
                                scale: 'medium',
                                hidden: me.hideGeneratePassword || me.newUser,
                                text: me.generatePasswordBtnText,
                                margins: '0 0 0 5'
                            }
                        ]
                    }
                ]
            }
        };

        if (me.passwordRotation) {
            defaults.header = false;
            defaults.onEsc = Ext.emptyFn;
        }

        me.config = Ext.apply(defaults, config || {});
        me.callParent([this.config]);

        me.addEvents(
            /**
             * @event generatePassword
             * @param {Modera.backend.security.toolscontribution.view.user.PasswordWindow} me
             */
            'generatePassword'
        );

        me.assignListeners();
    },

    loadData: function(data) {
        var me = this;
        me.setTitle(Ext.String.format(me.recordTitleText, Ext.util.Format.htmlEncode(data['username'])));
        me.down('form').getForm().setValues(data);
    },

    setPassword: function(plainPassword) {
        var me = this;
        var form = me.down('form').getForm();
        form.findField('plainPassword').setValue(plainPassword);
        form.findField('_plainPassword').setValue(plainPassword);
    },

    // private
    checkPasswords: function() {
        var me        = this;
        var form      = me.down('form').getForm();
        var saveBtn   = me.down('#saveBtn');
        var password  = form.findField('plainPassword');
        var _password = form.findField('_plainPassword');

        if (_password.getValue()) {
            if (password.getValue() != _password.getValue()) {
                form.markInvalid([{
                    id: _password.getName(),
                    msg: me.wrongPasswordText
                }]);
                saveBtn.disable();
            }
            else {
                form.clearInvalid();
                saveBtn.enable();
            }
        }
    },

    // private
    assignListeners: function() {
        var me = this;

        Ext.each(me.query('textfield[inputType=password]'), function(field) {
            field.on('change', function(field, newValue) {
                me.checkPasswords();
            });
            field.on('keyup', function(field, e) {
                var value = field.getValue();
                setTimeout(function() {
                    if (field.getValue() == value) {
                        me.checkPasswords();
                    }
                }, 200);
            });
        });

        me.down('#generatePassword').on('click', function(btn) {
            me.fireEvent('generatePassword', me);
        });
    }
});