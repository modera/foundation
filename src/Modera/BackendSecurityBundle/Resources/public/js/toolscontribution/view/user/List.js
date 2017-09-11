/**
 * @author Sergei Vizel <sergei.vizel@modera.org>
 */
Ext.define('Modera.backend.security.toolscontribution.view.user.List', {
    extend: 'Ext.grid.Panel',
    alias: 'widget.modera-backend-security-user-list',

    requires: [
        'Modera.backend.security.toolscontribution.store.Users',
        'MFC.HasSelectionAwareComponentsPlugin',
        'Ext.menu.Menu'
    ],

    plugins: [Ext.create('MFC.HasSelectionAwareComponentsPlugin')],

    // l10n
    firstNameColumnHeaderText: 'First name',
    lastNameColumnHeaderText: 'Last name',
    usernameColumnHeaderText: 'Principal',
    emailColumnHeaderText: 'Email',
    stateColumnHeaderText: 'State',
    groupsColumnHeaderText: 'Membership',
    addBtnText: 'New user',
    editBtnText: 'Edit selected',
    groupsBtnText: 'Group membership...',
    changePasswordBtnText: 'Change password',
    deleteBtnText: 'Delete',
    enableBtnText: 'Enable user',
    disableBtnText: 'Disable user',
    stateNewText: 'New',
    stateActiveText: 'Active',
    stateInactiveText: 'Inactive',

    // override
    constructor: function(config) {
        var me = this;

        config = config || {};

        var store = config.store || Ext.create('Modera.backend.security.toolscontribution.store.Users');

        var defaults = {
            tid: 'usersOverviewView',
            rounded: true,
            border: true,
            monitorModel: 'modera.security_bundle.user',
            emptyCls: 'mfc-grid-empty-text',
            store: store,
            selType: 'checkboxmodel',
            columns: [
                {
                    width: 160,
                    text: me.firstNameColumnHeaderText,
                    dataIndex: 'firstName',
                    renderer: me.defaultRenderer()
                },
                {
                    width: 160,
                    text: me.lastNameColumnHeaderText,
                    dataIndex: 'lastName',
                    renderer: me.defaultRenderer()
                },
                {
                    width: 160,
                    text: me.usernameColumnHeaderText,
                    dataIndex: 'username',
                    renderer: me.defaultRenderer()
                },
                {
                    width: 260,
                    text: me.emailColumnHeaderText,
                    dataIndex: 'email',
                    renderer: me.defaultRenderer()
                },
                {
                    width: 80,
                    text: me.stateColumnHeaderText,
                    dataIndex: 'state',
                    renderer: function(v, m, r) {
                        var state = 'Inactive';
                        if (r.get('isActive')) {
                            state = 1 === v ? 'Active' : 'New';
                        }
                        return me['state' + state + 'Text'];
                    }
                },
                {
                    flex: 1,
                    sortable: false,
                    text: me.groupsColumnHeaderText,
                    dataIndex: 'groups',
                    renderer: me.defaultRenderer()
                }
            ],
            dockedItems: [
                {
                    security: {
                        role: 'ROLE_MANAGE_USER_PROFILES',
                        strategy: 'hide'
                    },
                    xtype: 'toolbar',
                    dock: 'top',
                    extensionPoint: 'userTopToolBar',
                    items: [
                        {
                            hidden: config.hideViewAwareComponents || false,
                            itemId: 'newRecordBtn',
                            iconCls: 'mfc-icon-add-24',
                            text: me.addBtnText,
                            scale: 'medium',
                            security: {
                                role: 'ROLE_MANAGE_USER_PROFILES',
                                strategy: 'hide'
                            },
                            tid: 'newuserbtn'
                        },
                        '->',
                        {
                            xtype: 'splitbutton',
                            disabled: true,
                            selectionAware: true,
                            multipleSelectionSupported: true,
                            itemId: 'editRecordBtn',
                            iconCls: 'mfc-icon-edit-24',
                            text: me.editBtnText,
                            scale: 'medium',
                            extensionPoint: 'userActions',
                            menu: Ext.create('Ext.menu.Menu', {
                                items: [
                                    {
                                        hidden: !!config.hideDeleteUserFunctionality,
                                        itemId: 'deleteBtn',
                                        text: me.deleteBtnText,
                                        iconCls: 'mfc-icon-delete-16',
                                        tid: 'deleteUserButton'
                                    },
                                    {
                                        hidden: true,
                                        selectionAware: function(selected) {
                                            if (1 == selected.length && !selected[0].get('isActive')) {
                                                return this.show();
                                            }
                                            this.hide();
                                        },
                                        itemId: 'enableBtn',
                                        text: me.enableBtnText,
                                        iconCls: 'mfc-icon-apply-16',
                                        tid: 'enableUserButton'
                                    },
                                    {
                                        hidden: true,
                                        selectionAware: function(selected) {
                                            if (1 == selected.length && selected[0].get('isActive')) {
                                                return this.show();
                                            }
                                            this.hide();
                                        },
                                        itemId: 'disableBtn',
                                        text: me.disableBtnText,
                                        iconCls: 'mfc-icon-error-16',
                                        tid: 'disableUserButton'
                                    }
                                ]
                            }),
                            tid: 'editUserButton'
                        },
                        {
                            disabled: true,
                            selectionAware: true,
                            multipleSelectionSupported: true,
                            itemId: 'editGroupsBtn',
                            iconCls: 'modera-backend-security-icon-group-24',
                            text: me.groupsBtnText,
                            scale: 'medium',
                            tid: 'modifygroupsbtn'
                        },
                        {
                            hidden: config.hideViewAwareComponents || false,
                            disabled: true,
                            selectionAware: true,
                            itemId: 'editPasswordBtn',
                            iconCls: 'modera-backend-security-icon-password-24',
                            text: me.changePasswordBtnText,
                            scale: 'medium',
                            tid: 'changepasswordbtn'
                        }
                    ]
                },
                {
                    xtype: 'pagingtoolbar',
                    store: store,
                    dock: 'bottom',
                    displayInfo: true
                }
            ]
        };

        me.config = Ext.apply(defaults, config || {});
        me.callParent([me.config]);

        me.addEvents(
            /**
             * @event newrecord
             * @param {Modera.backend.security.toolscontribution.view.user.List} me
             */
            'newrecord',
            /**
             * @event editrecord
             * @param {Modera.backend.security.toolscontribution.view.user.List} me
             * @param {Object} params
             */
            'editrecord',
            /**
             * @event editpassword
             * @param {Modera.backend.security.toolscontribution.view.user.List} me
             * @param {Object} params
             */
            'editpassword',
            /**
             * @event editgroups
             * @param {Modera.backend.security.toolscontribution.view.user.List} me
             * @param {Object} params
             */
            'editgroups'
        );

        me.assignListeners();
    },

    // private
    defaultRenderer: function(msg) {
        return function(value, m, r) {
            if (Ext.isEmpty(value)) {
                return '<span class="mfc-empty-text">' + (msg || '-') + '</span>';
            } else if (!r.get('isActive')) {
                return '<span class="modera-backend-security-user-disabled">' + Ext.util.Format.htmlEncode(value) + '</span>';
            }

            return Ext.util.Format.htmlEncode(value);
        };
    },

    // private
    getSelectedRecord: function() {
        return this.getSelectedRecords()[0];
    },

    // private
    getSelectedRecords: function() {
        return this.getSelectionModel().getSelection();
    },

    // private
    getSelectedIds: function() {
        var records = this.getSelectedRecords();

        var ids = [];
        Ext.each(records, function(record) {
            ids.push(record.get('id'));
        });

        return ids;
    },

    // private
    assignListeners: function() {
        var me = this;

        var firstLoad = true;
        me.getStore().on('load', function(store, records) {
            if (!firstLoad) {
                var selected = [];
                var selectedIds = me.getSelectedIds();
                Ext.Array.each(records, function(record, index) {
                    if (-1 !== selectedIds.indexOf(record.get('id'))) {
                        selected.push(record);
                    }
                });
                me.getSelectionModel().select(selected);
            }
            firstLoad = false;
        });

        me.down('#newRecordBtn').on('click', function() {
            me.fireEvent('newrecord', me);
        });

        me.on('selectionchange', function() {
            var btn = me.down('#editRecordBtn');
            if (me.getSelectedRecords().length > 1) {
                btn.btnEl.addCls('modera-backend-security-btn-disabled');
            } else {
                btn.btnEl.removeCls('modera-backend-security-btn-disabled');
            }
        });

        me.down('#editRecordBtn').on('click', function(btn) {
            var records = me.getSelectedRecords();
            if (records.length > 1) {
                btn.maybeShowMenu();
            } else {
                var record = records[0];
                console.log('record meta', record.get('meta') );
                me.fireEvent('editrecord', me, { id: record.get('id'), meta: record.get('meta') });
            }

        });

        me.down('#deleteBtn').on('click', function() {
            var ids = me.getSelectedIds();
            me.fireEvent('deleterecord', me, { id: ids.length > 1 ? ids : ids[0] });
        });

        me.down('#enableBtn').on('click', function() {
            var record = me.getSelectedRecord();
            me.fireEvent('enableprofile', me, { id: record.get('id') });
        });

        me.down('#disableBtn').on('click', function() {
            var record = me.getSelectedRecord();
            me.fireEvent('disableprofile', me, { id: record.get('id') });
        });

        me.down('#editPasswordBtn').on('click', function() {
            var record = me.getSelectedRecord();
            me.fireEvent('editpassword', me, { id: record.get('id'), meta: record.get('meta') });
        });

        me.down('#editGroupsBtn').on('click', function() {
            var ids = me.getSelectedIds();
            me.fireEvent('editgroups', me, { id: ids.length > 1 ? ids : ids[0] });
        });
    }
});