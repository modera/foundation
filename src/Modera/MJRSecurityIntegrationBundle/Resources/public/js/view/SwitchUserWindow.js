/**
 * @author Sergei Vizel <sergei.vizel@modera.org>
 */
Ext.define('Modera.mjrsecurityintegration.view.SwitchUserWindow', {
    extend: 'MFC.window.ModalWindow',
    alias: 'widget.modera-mjrsecurityintegration-switchuserwindow',

    // l10n
    titleText: 'Switch user to',
    firstNameColumnHeaderText: 'First name',
    lastNameColumnHeaderText: 'Last name',
    usernameColumnHeaderText: 'Principal',

    // override
    constructor: function(config) {
        var me = this;

        var store = Ext.create('Ext.data.DirectStore', {
            fields: [
                'id', 'firstName', 'lastName', 'username'
            ],
            proxy: {
                type: 'direct',
                directFn: Actions.ModeraMJRSecurityIntegration_Index.backendUsersList,
                reader: {
                    root: 'items'
                }
            },
            autoLoad: true
        });

        var defaults = {
            title: me.titleText,
            width: 500,
            height: 400,
            bodyPadding: '0 0 10 0',
            layout: {
                type: 'vbox',
                pack: 'center',
                align:'stretch'
            },
            items: [
                {
                    xtype: 'grid',
                    flex:1,
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
            ],
            actions: [
                '->'
            ]
        };

        this.callParent([Ext.apply(defaults, config || {})]);

        this.assignListeners();
    },

    // private
    assignListeners: function() {
        var me = this;

        me.down('grid').on('select', function(selModel, record) {
            me.fireEvent('switchuser', me, record.get('username'));
        });
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
