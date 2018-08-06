/**
 * @author Sergei Vizel <sergei.vizel@modera.org>
 */
Ext.define('Modera.backend.languages.view.List', {
    extend: 'Ext.panel.Panel',
    alias: 'widget.modera-backend-languages-list',

    requires: [
        'MFC.grid.TouchPanel',
        'MFC.container.Header',
        'Modera.backend.languages.store.Languages'
    ],

    // l10n
    addBtnText: 'Add',
    editBtnText: 'Edit',
    localeHeaderText: 'Locale',
    nameHeaderText: 'Name',
    removeHeaderText: 'Remove',
    emptyListText: 'No items found',

    // override
    constructor: function(config) {
        var me = this;

        var defaults = {
            cls: 'modera-backend-languages',
            basePanel: false,
            border: true,
            padding: 0,
            layout: 'fit',
            items: [
                {
                    hideHeaders: true,
                    xtype: 'mfc-touchgrid',
                    monitorModel: 'modera.languages_bundle.language',
                    plugins: [ Ext.create('MFC.HasSelectionAwareComponentsPlugin') ],
                    store: Ext.create('Modera.backend.languages.store.Languages', { autoLoad: true }),
                    viewConfig: {
                        loadMask: false,
                        preserveScrollOnRefresh: true
                    },
                    columns: [
                        {
                            flex: 2,
                            dataIndex: 'name',
                            text: me.nameHeaderText,
                            renderer: me.defaultRenderer()
                        },
                        {
                            flex: 1,
                            align : 'right',
                            dataIndex: 'locale',
                            text : me.localeHeaderText,
                            renderer: me.defaultRenderer()
                        }//,
                        // {
                        //     align : 'center',
                        //     xtype : 'actioncolumn',
                        //     text : me.removeHeaderText,
                        //     defaultRenderer: me.actionColumnRenderer(),
                        //     items : [
                        //         {
                        //             glyph: 'trash',
                        //             //tooltip : me.removeHeaderText,
                        //             handler : function (grid, rowIndex, colIndex, item, e, record) {
                        //                 me.fireEvent('deleterecord', me, { id: record.get('id') });
                        //             }
                        //         }
                        //     ]
                        // }
                    ],
                    emptyText: me.emptyListText,
                    emptyCls: 'mfc-grid-empty-text',
                    listeners: {
                        'afterrender': function(grid) {
                            grid.view.refresh();
                        }
                    },
                    dockedItems: [
                        {
                            dock: 'top',
                            xtype: 'toolbar',
                            items: [
                                {
                                    scale: 'medium',
                                    text: me.addBtnText,
                                    iconCls: 'mfc-icon-add-24',
                                    handler: function(btn) {
                                        me.fireEvent('newrecord', me, {});
                                    }
                                },
                                {
                                    disabled: true,
                                    scale: 'medium',
                                    text: me.editBtnText,
                                    selectionAware: true,
                                    iconCls: 'mfc-icon-edit-24',
                                    handler: function(btn) {
                                        var record = me.getSelectedRecord();
                                        me.fireEvent('editrecord', me, { id: record.get('id') });
                                    }
                                },
                                '->'
                            ]
                        }
                    ]
                }
            ]
        };
        me.config = Ext.apply(defaults, config);
        me.callParent([me.config]);

        me.assignListeners();
    },

    // private
    defaultRenderer: function(msg) {
        return function(value, m, r) {
            m.tdCls += ' highlight';

            if (Ext.isEmpty(value)) {
                return '<span class="mfc-empty-text">' + (msg || '-') + '</span>';
            } else if (!r.get('isEnabled')) {
                return '<span class="inactive">' + Ext.util.Format.htmlEncode(value) + '</span>';
            }

            return Ext.util.Format.htmlEncode(value);
        };
    },

    // private
    actionColumnRenderer: function() {
        return function(value, m, r) {
            m.tdCls += ' ';

            var ret = '';
            for (var i = 0; i < this.items.length; i++) {
                var item = this.items[i];

                var spanCls = 'x-action-col-icon x-action-col-' + String(i) + ' glyph-ico gray';
                var tooltip = item.tooltip ? 'data-qtip="' + item.tooltip + '"' : '';
                var glyph = FontAwesome.resolve(item.glyph);

                ret += [
                    '<span role="button" class="' + spanCls + '" ' + tooltip + '>',
                        '&#' + glyph.split('@')[0] + ';',
                    '</span>'
                ].join('');
            }
            return ret;
        };
    },

    // private
    getSelectedRecord: function() {
        var me = this;

        return me.getSelectedRecords()[0];
    },

    // private
    getSelectedRecords: function() {
        var me = this;

        return me.down('grid').getSelectionModel().getSelection();
    },

    // private
    assignListeners: function() {
        var me = this;

        me.down('grid').on('itemdblclick', function() {
            var record = me.getSelectedRecord();
            me.fireEvent('editrecord', me, { id: record.get('id') });
        });
    }
});
