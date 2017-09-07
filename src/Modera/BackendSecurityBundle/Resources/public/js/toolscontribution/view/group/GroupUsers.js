/**
 * @author Sergei Lissovski <sergei.lissovski@modera.org>
 */
Ext.define('Modera.backend.security.toolscontribution.view.group.GroupUsers', {
    extend: 'Ext.panel.Panel',
    alias: 'widget.modera-backend-security-group-groupusers',

    requires: [
        'Modera.backend.security.toolscontribution.store.GroupUsers',
        'Modera.backend.security.toolscontribution.view.user.List'
    ],

    // l10n
    noGroupSelectedText: 'No group selected',
    userIdColumnText: 'User ID',
    fullNameColumnText: 'Full name',
    stateColumnText: 'State',
    stateNewText: 'New',
    stateActiveText: 'Active',
    stateInactiveText: 'Inactive',

    // override
    constructor: function(config) {
        var me = this;

        var defaults = {
            layout: 'card',
            items: [
                {
                    itemId: 'placeholder',
                    xtype: 'grid',
                    border: true,
                    hideHeaders: true,
                    emptyText: me.noGroupSelectedText,
                    emptyCls: 'mfc-grid-empty-text',
                    columns: [],
                    listeners: {
                        'afterrender': function(grid) {
                            grid.view.refresh();
                        }
                    }
                },
                {
                    itemId: 'users',
                    xtype: 'modera-backend-security-user-list',
                    hideViewAwareComponents: true,
                    hideDeleteUserFunctionality: config.hideDeleteUserFunctionality,
                    columns: [
                        {
                            flex: 1,
                            text: me.userIdColumnText,
                            dataIndex: 'username',
                            renderer: me.defaultRenderer()
                        },
                        {
                            flex: 1,
                            text: me.fullNameColumnText,
                            dataIndex: 'fullname',
                            renderer: me.defaultRenderer()
                        },
                        {
                            width: 80,
                            text: me.stateColumnText,
                            dataIndex: 'state',
                            renderer: function(v, m, r) {
                                var state = 'Inactive';
                                if (r.get('isActive')) {
                                    state = 1 === v ? 'Active' : 'New';
                                }
                                return me['state' + state + 'Text'];
                            }
                        }
                    ],
                    store: Ext.create('Modera.backend.security.toolscontribution.store.GroupUsers')
                }
            ]
        };

        me.config = Ext.apply(defaults, config || {});
        me.callParent([me.config]);

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
    assignListeners: function() {
        this.relayEvents(this.down('modera-backend-security-user-list'), [
            'editrecord', 'deleterecord', 'editgroups', 'enableprofile', 'disableprofile'
        ]);
    }
});