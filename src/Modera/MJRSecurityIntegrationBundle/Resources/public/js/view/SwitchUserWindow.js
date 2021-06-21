/**
 * @author Sergei Vizel <sergei.vizel@modera.org>
 */
Ext.define('Modera.mjrsecurityintegration.view.SwitchUserWindow', {
    extend: 'MFC.window.ModalWindow',
    alias: 'widget.modera-mjrsecurityintegration-switchuserwindow',

    requires: [
        'MFC.form.field.plugin.FieldInputFinishedPlugin'
    ],

    // l10n
    titleText: 'Switch user to',
    firstNameColumnHeaderText: 'First name',
    lastNameColumnHeaderText: 'Last name',
    usernameColumnHeaderText: 'Principal',
    typeToFilterText: 'type here to filter...',

    // override
    constructor: function(config) {
        var me = this;

        var store = Ext.create('Ext.data.DirectStore', {
            fields: [
                'id', 'firstName', 'lastName', 'username'
            ],
            pageSize: 10,
            remoteSort: true,
            remoteFilter: true,
            proxy: {
                type: 'direct',
                directFn: config['switchUserListAction'],
                reader: {
                    root: 'items'
                }
            },
            autoLoad: true
        });

        var defaults = {
            title: me.titleText,
            width: 550,
            height: 450,
            maxHeight: Ext.getBody().getViewSize().height - 60,
            closeOnOuterClick: true,
            hideCloseButton: true,
            bodyPadding: '0 0 0 0',
            layout: {
                type: 'vbox',
                pack: 'center',
                align:'stretch'
            },
            dockedItems: [
                {
                    dock: 'top',
                    xtype: 'form',
                    defaults: {
                        fieldStyle: {
                            margin: '0 0 5 0'
                        }
                    },
                    items: [
                        {
                            width: '100%',
                            itemId: 'filter',
                            xtype: 'textfield',
                            plugins: [
                                Ext.create('MFC.form.field.plugin.FieldInputFinishedPlugin', {
                                    timeout: 800
                                })
                            ],
                            emptyText: me.typeToFilterText,
                            enableKeyEvents: true,
                            value: ''
                        }
                    ]
                }
            ],
            items: [
                {
                    flex:1,
                    xtype: 'grid',
                    border: true,
                    viewConfig: {
                        markDirty: false
                    },
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
                            flex: 1,
                            text: me.usernameColumnHeaderText,
                            dataIndex: 'username',
                            renderer: me.defaultRenderer()
                        }
                    ],
                    dockedItems: [
                        {
                            xtype: 'pagingtoolbar',
                            store: store,
                            dock: 'bottom',
                            displayInfo: true
                        }
                    ],
                    store: store
                }
            ]
        };

        me.callParent([Ext.apply(defaults, config || {})]);

        me.assignListeners();
    },

    // private
    assignListeners: function() {
        var me = this;

        me.getGrid().on('select', function(selModel, record) {
            me.fireEvent('switchuser', me, record.get('username'));
        });

        var field = me.getFilterField();

        field.on('focus', function(field) { field.selectText(); });
        field.on('inputfinished', me.onFilterChanged, me);
    },

    // private
    getGrid: function() {
        return this.down('grid')
    },

    // private
    getStore: function() {
        return this.getGrid().getStore();
    },

    // private
    getFilterField: function() {
        return this.down('#filter');
    },

    // private
    onFilterChanged: function() {
        var me = this;

        var store = me.getStore();

        var field = me.getFilterField();
        if (field.getValue()) {
            store.filter([
                {
                    property: 'name',
                    value: field.getValue()
                }
            ]);
        } else {
            store.clearFilter();
        }
    },

    // private
    defaultRenderer: function(msg) {
        return function(value, m, r) {
            if (Ext.isEmpty(value)) {
                return '<span class="mfc-empty-text">' + (msg || '-') + '</span>';
            }

            return Ext.util.Format.htmlEncode(value);
        };
    }
});
