/**
 * @copyright 2014 Modera Foundation
 */
Ext.define('Modera.backend.security.toolscontribution.view.user.List', {
    extend: 'Ext.grid.Panel',
    alias: 'widget.modera-backend-security-user-list',

    requires: [
        'MFC.Date',
        'MFC.HasSelectionAwareComponentsPlugin',
        'Modera.backend.security.toolscontribution.store.Users',
        'Ext.menu.Menu'
    ],

    plugins: [Ext.create('MFC.HasSelectionAwareComponentsPlugin')],

    // l10n
    personalIdColumnHeaderText: 'Personal ID',
    firstNameColumnHeaderText: 'First name',
    lastNameColumnHeaderText: 'Last name',
    usernameColumnHeaderText: 'Principal',
    emailColumnHeaderText: 'Email',
    stateColumnHeaderText: 'State',
    lastLoginColumnHeaderText: 'Last login',
    groupsColumnHeaderText: 'Membership',
    addBtnText: 'User',
    editBtnText: 'Edit selected',
    groupsBtnText: 'Groups',
    permissionsBtnText: 'Permissions',
    changePasswordBtnText: 'Password',
    deleteBtnText: 'Remove',
    enableBtnText: 'Enable user',
    disableBtnText: 'Disable user',
    stateNewText: 'New',
    stateActiveText: 'Active',
    stateInactiveText: 'Inactive',
    directPermissionsText: 'Direct permissions: {0}',
    filterPlaceholderText: 'type here to filter...',
    resetFiltersBtnText: 'Reset',
    noneText: '-- None --',

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
                    hidden: true,
                    text: me.personalIdColumnHeaderText,
                    dataIndex: 'personalId',
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
                    width: 160,
                    hidden: true,
                    text: me.lastLoginColumnHeaderText,
                    dataIndex: 'lastLogin',
                    renderer: me.defaultRenderer(null, function(value) {
                        return MFC.Date.format(value, 'datetime');
                    })
                },
                {
                    flex: 1,
                    sortable: false,
                    text: me.groupsColumnHeaderText,
                    dataIndex: 'groups',
                    renderer: (function() {
                        var defaultRenderer = me.defaultRenderer(null, function(value) {
                            return value;
                        });
                        return function(v, m, r) {
                            var value = Ext.util.Format.htmlEncode(v.join(', '));

                            var permissionsCount = r.get('permissions').length;
                            if (permissionsCount > 0) {
                                var glyph = FontAwesome.resolve('shield-alt', 'fas');
                                var tooltip = Ext.String.format(me.directPermissionsText, permissionsCount);

                                var stl = [
                                    'font-size: 14px;',
                                    'font-family: ' + glyph.split('@')[1] + ';'
                                ].join(' ');

                                var icon = [
                                    '<span style="' + stl + '" data-qtip="' + tooltip + '">',
                                        '&#' + glyph.split('@')[0] + ';',
                                    '</span>'
                                ].join('');

                                value = [ icon, value ].join(' ').trim();
                            }

                            return defaultRenderer(value, m, r);
                        };
                    })()
                }
            ],
            dockedItems: [
                {
                    security: {
                        role: function(roles, callback) {
                            callback(['ROLE_MANAGE_USER_ACCOUNTS', 'ROLE_MANAGE_USER_PROFILES', 'ROLE_MANAGE_USER_PROFILE_INFORMATION'].filter(function(role) {
                                    return roles.indexOf(role) > -1;
                                }).length > 0);
                        },
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
                                role: 'ROLE_MANAGE_USER_ACCOUNTS',
                                strategy: 'hide'
                            },
                            handler: function(btn) {
                                me.fireEvent('newrecord', me);
                            },
                            tid: 'newUserBtn'
                        },
                        '->',
                        {
                            xtype: 'combo',
                            itemId: 'stateFilter',
                            selectionAware: function(selected) {
                                this.applyVisibility = selected.length < 1;
                                this.setVisible(this.applyVisibility);
                            },
                            name: 'state',
                            editable: false,
                            emptyText: me.stateColumnHeaderText,
                            store: Ext.create('Ext.data.Store', {
                                fields: ['id', 'name'],
                                data : [
                                    {
                                        id: null,
                                        name: me.noneText
                                    },
                                    {
                                        id: 0,
                                        name: me.stateNewText
                                    },
                                    {
                                        id: 1,
                                        name: me.stateActiveText
                                    },
                                    {
                                        id: -1,
                                        name: me.stateInactiveText
                                    }
                                ]
                            }),
                            listeners: {
                                change: function(combo, newValue, oldValue) {
                                    if (null === newValue) {
                                        combo.reset();
                                    }
                                }
                            },
                            queryMode: 'local',
                            displayField: 'name',
                            valueField: 'id',
                            tid: 'stateComboBox'
                        },
                        {
                            xtype: 'combo',
                            itemId: 'groupFilter',
                            hidden: config.hideViewAwareComponents || false,
                            selectionAware: function(selected) {
                                if (!config.hideViewAwareComponents) {
                                    this.applyVisibility = selected.length < 1;
                                    this.setVisible(this.applyVisibility);
                                }
                            },
                            name: 'group',
                            editable: false,
                            emptyText: me.groupsColumnHeaderText,
                            store: Ext.create('Modera.backend.security.toolscontribution.store.Groups', {
                                autoLoad: false,
                                listeners: {
                                    load: function(store, records) {
                                        store.insert(0, { id: null, name: me.noneText });
                                    }
                                }
                            }),
                            listeners: {
                                change: function(combo, newValue, oldValue) {
                                    if (null === newValue) {
                                        combo.reset();
                                    }
                                }
                            },
                            displayField: 'name',
                            valueField: 'id',
                            tid: 'groupComboBox'
                        },
                        {
                            flex: 1,
                            height: 30,
                            itemId: 'inputFilter',
                            xtype: 'textfield',
                            selectionAware: function(selected) {
                                this.applyVisibility = selected.length < 1;
                                this.setVisible(this.applyVisibility);
                            },
                            emptyText: this.filterPlaceholderText,
                            listeners: {
                                keyup: function(field) {
                                    if (field.__timeoutId) {
                                        clearTimeout(field.__timeoutId);
                                    }
                                    field.__timeoutId = setTimeout(function() {
                                        if (field.__searchValue !== field.getValue()) {
                                            field.__searchValue = field.getValue();
                                            field.fireEvent('inputfinished', field);
                                        }
                                    }, 800);
                                }
                            },
                            enableKeyEvents: true,
                            tid: 'searchField'
                        },
                        {
                            selectionAware: function(selected) {
                                this.applyVisibility = selected.length < 1;
                                this.setVisible(this.applyVisibility);
                            },
                            handler: function() {
                                me.resetFilters();
                                me.applyFilters();
                            },
                            text: me.resetFiltersBtnText,
                            scale: 'medium',
                            tid: 'resetFiltersButton'
                        },
                        {
                            hidden: true,
                            xtype: 'splitbutton',
                            handleSecurity: function(securityMgr, application) {
                                var btn = this;
                                securityMgr.isAllowed(function(roles, callback) {
                                    callback(['ROLE_MANAGE_USER_PROFILES', 'ROLE_MANAGE_USER_PROFILE_INFORMATION'].filter(function(role) {
                                        return roles.indexOf(role) > -1;
                                    }).length > 0);
                                }, function(isAllowed) {
                                    btn.isAllowed = isAllowed;
                                    btn.setVisible(btn.isAllowed && btn.applyVisibility);
                                });
                            },
                            selectionAware: function(selected) {
                                this.applyVisibility = selected.length > 0;
                                this.setVisible(this.isAllowed && this.applyVisibility);
                            },
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
                                        security: {
                                            role: 'ROLE_MANAGE_USER_ACCOUNTS',
                                            strategy: 'hide'
                                        },
                                        tid: 'deleteUserButton'
                                    },
                                    {
                                        hidden: true,
                                        handleSecurity: function(securityMgr, application) {
                                            var btn = this;
                                            securityMgr.isAllowed('ROLE_MANAGE_USER_PROFILES', function(isAllowed) {
                                                btn.isAllowed = isAllowed;
                                                btn.setVisible(btn.isAllowed && btn.applyVisibility);
                                            });
                                        },
                                        selectionAware: function(selected) {
                                            this.applyVisibility = 1 == selected.length && !selected[0].get('isActive');
                                            this.setVisible(this.isAllowed && this.applyVisibility);
                                        },
                                        itemId: 'enableBtn',
                                        text: me.enableBtnText,
                                        iconCls: 'mfc-icon-apply-16',
                                        tid: 'enableUserButton'
                                    },
                                    {
                                        hidden: true,
                                        handleSecurity: function(securityMgr, application) {
                                            var btn = this;
                                            securityMgr.isAllowed('ROLE_MANAGE_USER_PROFILES', function(isAllowed) {
                                                btn.isAllowed = isAllowed;
                                                btn.setVisible(btn.isAllowed && btn.applyVisibility);
                                            });
                                        },
                                        selectionAware: function(selected) {
                                            this.applyVisibility = 1 == selected.length && selected[0].get('isActive');
                                            this.setVisible(this.isAllowed && this.applyVisibility);
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
                            hidden: true,
                            handleSecurity: function(securityMgr, application) {
                                var btn = this;
                                securityMgr.isAllowed('ROLE_MANAGE_USER_PROFILES', function(isAllowed) {
                                    btn.isAllowed = isAllowed;
                                    btn.setVisible(btn.isAllowed && btn.applyVisibility);
                                });
                            },
                            selectionAware: function(selected) {
                                this.applyVisibility = selected.length > 0;
                                this.setVisible(this.isAllowed && this.applyVisibility);
                            },
                            multipleSelectionSupported: true,
                            itemId: 'editGroupsBtn',
                            iconCls: 'modera-backend-security-icon-group-24',
                            text: me.groupsBtnText,
                            scale: 'medium',
                            tid: 'modifyGroupsBtn'
                        },
                        {
                            hidden: true,
                            disabled: true,
                            selectionAware: function(selected) {
                                this.applyVisibility = selected.length > 0 && !config.hideViewAwareComponents;
                                this.setVisible(this.applyVisibility);
                                this.setDisabled(1 != selected.length);
                            },
                            itemId: 'editPermissionsBtn',
                            iconCls: 'modera-backend-security-icon-permission-24',
                            text: me.permissionsBtnText,
                            scale: 'medium',
                            tid: 'editPermissionsButton'
                        },
                        {
                            hidden: true,
                            disabled: true,
                            handleSecurity: function(securityMgr, application) {
                                var btn = this;
                                securityMgr.isAllowed('ROLE_MANAGE_USER_PROFILES', function(isAllowed) {
                                    btn.isAllowed = isAllowed;
                                    btn.setVisible(btn.isAllowed && btn.applyVisibility);
                                });
                            },
                            selectionAware: function(selected) {
                                this.applyVisibility = selected.length > 0 && !config.hideViewAwareComponents;
                                this.setVisible(this.isAllowed && this.applyVisibility);
                                this.setDisabled(1 != selected.length);
                            },
                            itemId: 'editPasswordBtn',
                            iconCls: 'modera-backend-security-icon-password-24',
                            text: me.changePasswordBtnText,
                            scale: 'medium',
                            tid: 'changePasswordBtn'
                        }
                    ]
                },
                {
                    xtype: 'pagingtoolbar',
                    store: store,
                    dock: 'bottom',
                    displayInfo: true,
                    items: [
                        {
                            itemId: 'pageSizeSeparator',
                            xtype: 'tbseparator'
                        },
                        {
                            itemId: 'pageSizeButtonsContainer',
                            xtype: 'container',
                            layout: 'hbox',
                            margin: '0 0 0 10',
                            defaults: {
                                xtype: 'button',
                                scale: 'medium',
                                enableToggle: true,
                                allowDepress: false,
                                toggleGroup: Ext.id(null, 'pageSizeToggle'),
                                margin: '0 5 0 0'
                            },
                            items: [
                                {
                                    text: '25',
                                    pressed: true,
                                    handler: function() {
                                        store.pageSize = 25;
                                        store.loadPage(1);
                                    }
                                },
                                {
                                    text: '50',
                                    handler: function() {
                                        store.pageSize = 50;
                                        store.loadPage(1);
                                    }
                                },
                                {
                                    text: '100',
                                    handler: function() {
                                        store.pageSize = 100;
                                        store.loadPage(1);
                                    }
                                }
                            ]
                        }
                    ],
                    listeners: {
                        afterrender: function(toolbar) {
                            var displayItem = toolbar.down('#displayItem');
                            var pageSizeSeparator = toolbar.down('#pageSizeSeparator');
                            var pageSizeButtonsContainer = toolbar.down('#pageSizeButtonsContainer');

                            function hide(component) {
                                if (component && component.el) {
                                    component.el.setStyle({
                                        opacity: '0',
                                        zIndex: '-1',
                                        pointerEvents: 'none'
                                    });
                                }
                            }

                            function show(component) {
                                if (component && component.el) {
                                    component.el.setStyle({
                                        opacity: null,
                                        zIndex: null,
                                        pointerEvents: null
                                    });
                                }
                            }

                            function updateVisibility() {
                                var availableWidth = toolbar.getWidth();

                                var padding = 10;
                                var baseWidth = parseFloat(pageSizeSeparator.el.getStyle('left')) + padding;
                                var pageSizeSeparatorWidth = pageSizeSeparator.getWidth() + padding;
                                var pageSizeButtonsContainerWidth = pageSizeButtonsContainer.getWidth() + padding;
                                var pageSizeWidth = pageSizeSeparatorWidth + pageSizeButtonsContainerWidth;
                                var displayItemWidth = displayItem.getWidth() + padding + 5;

                                var pageSizeVisible = availableWidth >= baseWidth + pageSizeWidth;
                                var displayItemVisible = availableWidth >= baseWidth + pageSizeWidth + displayItemWidth;

                                displayItemVisible ? show(displayItem) : hide(displayItem);
                                pageSizeVisible ? show(pageSizeSeparator) : hide(pageSizeSeparator);
                                pageSizeVisible ? show(pageSizeButtonsContainer) : hide(pageSizeButtonsContainer);
                            }

                            toolbar.on('resize', updateVisibility);
                            setTimeout(updateVisibility, 100);
                        }
                    }
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
             * @event editpermissions
             * @param {Modera.backend.security.toolscontribution.view.user.List} me
             * @param {Object} params
             */
            'editpermissions',
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
    defaultRenderer: function(msg, valueFormatter) {
        if (!valueFormatter) {
            valueFormatter = Ext.util.Format.htmlEncode;
        }
        return function(value, m, r) {
            if (Ext.isEmpty(value)) {
                return '<span class="mfc-empty-text">' + (msg || '-') + '</span>';
            }
            value = valueFormatter(value);
            if (!r.get('isActive')) {
                return '<span class="modera-backend-security-user-disabled">' + value + '</span>';
            }
            return value;
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

        me.down('#newRecordBtn').on('click', function(btn) {
            // return false;
        });

        me.down('#stateFilter').on('change', function(combo, newValue) {
            me.applyFilters();
        });
        me.down('#groupFilter').on('change', function(combo, newValue) {
            me.applyFilters();
        });
        me.down('#inputFilter').on('inputfinished', function(field) {
            me.applyFilters();
        });

        me.on('selectionchange', function() {
            var btn = me.down('#editRecordBtn');
            if (!btn.isAllowed || me.getSelectedRecords().length > 1) {
                btn.btnEl.addCls('modera-backend-security-btn-disabled');
            } else {
                btn.btnEl.removeCls('modera-backend-security-btn-disabled');
            }
        });

        me.down('#editRecordBtn').on('click', function(btn) {
            var records = me.getSelectedRecords();
            if (!btn.isAllowed || records.length > 1) {
                btn.maybeShowMenu();
            } else {
                var record = records[0];
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

        me.down('#editPermissionsBtn').on('click', function() {
            var record = me.getSelectedRecord();
            me.fireEvent('editpermissions', me, { id: record.get('id') });
        });

        me.down('#editPasswordBtn').on('click', function() {
            var record = me.getSelectedRecord();
            me.fireEvent('editpassword', me, { id: record.get('id'), meta: record.get('meta') });
        });

        me.down('#editGroupsBtn').on('click', function() {
            var ids = me.getSelectedIds();
            me.fireEvent('editgroups', me, { id: ids.length > 1 ? ids : ids[0] });
        });
    },

    applyFilters: function() {
        var me = this;

        var filters = [];

        var state = me.down('#stateFilter').getValue();
        if (null !== state) {
            filters.push({ property: 'isActive', value: 'eq:' + (state >= 0 ? 'true' : 'false') });
            if (state >= 0) {
                filters.push({ property: 'state', value: 'eq:' + state });
            }
        }

        var group = me.down('#groupFilter').getValue();
        if (null !== group) {
            filters.push({ property: 'groups', value: 'in:' + group });
        }

        var search = me.down('#inputFilter').getValue();
        if (search) {
            filters.push([
                { property: 'firstName', value: 'like:%' + search + '%' },
                { property: 'lastName', value: 'like:%' + search + '%' },
                { property: 'username', value: 'like:%' + search + '%' },
                { property: 'email', value: 'like:%' + search + '%' }
            ]);
        }

        me.getStore().applyFilters(filters);
    },

    resetFilters: function() {
        var me = this;

        var stateFilter = me.down('#stateFilter');
        stateFilter.suspendEvents();
        stateFilter.reset();
        stateFilter.resumeEvents();

        var groupFilter = me.down('#groupFilter');
        groupFilter.suspendEvents();
        groupFilter.reset();
        groupFilter.resumeEvents();

        var inputFilter = me.down('#inputFilter');
        inputFilter.suspendEvents();
        inputFilter.reset();
        inputFilter.__searchValue = '';
        inputFilter.resumeEvents();
    }
});
