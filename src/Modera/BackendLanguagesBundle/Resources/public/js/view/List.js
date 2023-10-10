/**
 * @author Sergei Vizel <sergei.vizel@modera.org>
 */
Ext.define('Modera.backend.languages.view.List', {
    extend: 'Ext.tab.Panel',
    alias: 'widget.modera-backend-languages-list',

    requires: [
        'MFC.panel.Message',
        'MFC.grid.TouchPanel',
        'MFC.container.Header',
        'Modera.backend.languages.store.Languages'
    ],

    // l10n
    addBtnText: 'Add',
    editBtnText: 'Edit',
    localeHeaderText: 'Locale',
    nameHeaderText: 'Name',
    defaultHeaderText: 'Default',
    removeHeaderText: 'Remove',
    emptyListText: 'No items found',
    languagesTabText: 'Languages',
    selectLanguageText: 'choose a language from left to start...',
    regionalSettingsLabelText: 'Regional settings',
    timeLabelText: 'Time',
    dateShortLabelText: 'Date short',
    dateLongLabelText: 'Date long',
    momentLabelText: 'Moment',
    priceLabelText: 'Price',

    // override
    constructor: function(config) {
        var me = this;

        var defaults = {
            extensionPoint: 'localisationSettings',
            cls: 'modera-backend-languages',
            basePanel: false,
            layout: 'fit',
            frame: true,
            padding: 0,
            items: [
                {
                    layout: 'fit',
                    itemId: 'languages',
                    title: me.languagesTabText,
                    dockedItems: [
                        {
                            width: 500,
                            dock: 'left',
                            hideHeaders: true,
                            xtype: 'mfc-touchgrid',
                            tid: 'languageGrid',
                            monitorModel: 'modera.languages_bundle.language',
                            plugins: [ Ext.create('MFC.HasSelectionAwareComponentsPlugin') ],
                            store: Ext.create('Modera.backend.languages.store.Languages', { autoLoad: true }),
                            viewConfig: {
                                loadMask: false,
                                preserveScrollOnRefresh: true
                            },
                            selModel: {
                                allowDeselect: true
                            },
                            columns: [
                                {
                                    flex: 2,
                                    dataIndex: 'name',
                                    text: me.nameHeaderText,
                                    renderer: me.defaultRenderer()
                                },
                                // {
                                //     flex: 1,
                                //     align : 'right',
                                //     dataIndex: 'locale',
                                //     text : me.localeHeaderText,
                                //     renderer: me.defaultRenderer()
                                // },
                                // {
                                //     align : 'center',
                                //     xtype : 'actioncolumn',
                                //     text : me.removeHeaderText,
                                //     defaultRenderer: me.actionColumnRenderer(),
                                //     items : [
                                //         {
                                //             glyph: 'trash-alt',
                                //             //tooltip : me.removeHeaderText,
                                //             handler : function (grid, rowIndex, colIndex, item, e, record) {
                                //                 me.fireEvent('deleterecord', me, { id: record.get('id') });
                                //             }
                                //         }
                                //     ]
                                // },
                                {
                                    width: 60,
                                    dataIndex: 'isDefault',
                                    text: me.defaultHeaderText,
                                    xtype: 'templatecolumn',
                                    tpl: new Ext.XTemplate(
                                        [
                                            '<span style="{[ this.glyphStyle(values) ]}">',
                                                '{[ this.glyph(values) ]}',
                                            '</span>'
                                        ].join(''),
                                        {
                                            glyph: function(values) {
                                                if (values.isDefault) {
                                                    var glyph = FontAwesome.resolve('asterisk', 'fas');
                                                    var glyphParts = glyph.split('@');
                                                    return '&#' + glyphParts[0] + ';';
                                                }
                                                return '';
                                            },
                                            glyphStyle: function(values) {
                                                if (values.isDefault) {
                                                    var glyph = FontAwesome.resolve('asterisk', 'fas');
                                                    var glyphParts = glyph.split('@');

                                                    return 'font-family: ' + glyphParts[1] + ';';
                                                }
                                                return '';
                                            }
                                        }
                                    )
                                }
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
                                            tid: 'addBtn',
                                            iconCls: 'mfc-icon-add-24',
                                            handler: function(btn) {
                                                me.fireEvent('newrecord', me, {});
                                            }
                                        },
                                        {
                                            disabled: true,
                                            scale: 'medium',
                                            text: me.editBtnText,
                                            tid: 'editBtn',
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
                    ],
                    items: [
                        {
                            itemId: 'details',
                            layout: 'fit',
                            border: true,
                            bodyStyle: {
                                borderBottomStyle: 'none',
                                borderRightStyle: 'none'
                            },
                            items: me.createLanguageNotSelectedView()
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
    createLanguageNotSelectedView: function() {
        var me = this;

        return {
            xtype: 'mfc-pmsg',
            msg: me.selectLanguageText,
            bodyStyle: {
                background: 'transparent'
            }
        };
    },

    // private
    createLanguageDetailsView: function(locale) {
        var me = this;

        var language = locale.split('_')[0];
        var now = MFC.Date.moment().locale(locale.split('_').join('-'));

        var grid = me.getGrid();
        var store = grid.getStore();
        var ignore = Ext.Array.map(store.data.filterBy(function(item) {
            return locale != item['data']['locale'];
        }).items, function(item) {
            return item['data']['locale'];
        });

        return {
            xtype: 'panel',
            autoScroll: true,
            items: {
                xtype: 'form',
                layout: 'anchor',
                margin: '20 0',
                defaults: {
                    anchor: '100%',
                    labelAlign: 'left',
                    padding: '0 30'
                },
                items: [
                    {
                        name: 'locale',
                        xtype: 'combo',
                        tid: 'regionCombobox',
                        labelAlign: 'top',
                        fieldLabel: me.regionalSettingsLabelText,
                        value: locale,
                        store: Ext.create('Modera.backend.languages.store.Locales', {
                            autoLoad: true,
                            extraParams: {
                                language: language,
                                ignore: ignore || []
                            }
                        }),
                        listConfig: {
                            getInnerTpl: function(displayField) {
                                return '{[Ext.util.Format.htmlEncode(values.' + displayField + ')]}';
                            }
                        },
                        queryMode: 'local',
                        displayField: 'name',
                        valueField: 'id',
                        allowBlank: false,
                        editable: false,
                        listeners: {
                            change: function(field, newValue, oldValue) {
                                var record = me.getSelectedRecord();
                                me.fireEvent('updaterecord', me, { id: record.get('id'), locale: newValue });
                            }
                        }
                    },
                    {
                        xtype: 'box',
                        style: {
                            margin: '30px 0 20px',
                            borderTop: '1px dashed #d0d0d0'
                        }
                    },
                    {
                        xtype: 'displayfield',
                        fieldLabel: me.timeLabelText,
                        value: now.format('LT')
                    },
                    {
                        xtype: 'displayfield',
                        fieldLabel: me.dateShortLabelText,
                        value: now.format('L')
                    },
                    {
                        xtype: 'displayfield',
                        fieldLabel: me.dateLongLabelText,
                        value: now.format('dddd, LL')
                    },
                    {
                        xtype: 'displayfield',
                        fieldLabel: me.momentLabelText,
                        tid: 'momentField',
                        value: now.fromNow()
                    },
                    {
                        xtype: 'displayfield',
                        fieldLabel: me.priceLabelText,
                        tid: 'priceField',
                        value: me.formatPrice(locale, 99999.99)
                    }
                ]
            }
        };
    },

    // private
    formatPrice: function(locale, value) {
        Ext.apply(Ext.util.Format, Ext.util.Format['_locales'][locale] || {});

        var currencyPrecision = Ext.util.Format.currencyPrecision;
        if (value % 1 === 0) {
            currencyPrecision = 0;
        }

        var price = Ext.util.Format.currency(value, Ext.util.Format.currencySign, currencyPrecision);

        Ext.apply(Ext.util.Format, Ext.util.Format['_default']);

        return price;
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
                var glyph = FontAwesome.resolve(item.glyph, 'fas');

                ret += [
                    '<span role="button" class="' + spanCls + '" ' + tooltip + '>',
                        '&#' + glyph.split('@')[0] + ';',
                    '</span>'
                ].join('');
            }
            return ret;
        };
    },

    // public
    getGrid: function() {
        return this.down('#languages grid');
    },

    // private
    getSelectedRecord: function() {
        return this.getSelectedRecords()[0];
    },

    // private
    getSelectedRecords: function() {
        return this.getGrid().getSelectionModel().getSelection();
    },

    // private
    assignListeners: function() {
        var me = this;

        var grid = me.getGrid();
        var store = grid.getStore();

        grid.on('selectionchange', function(selModel, selected) {
            var details = me.down('#details');
            details.removeAll();

            if (selected.length) {
                var record = selected[0];
                details.add(me.createLanguageDetailsView(record.get('locale')));
            } else {
                details.add(me.createLanguageNotSelectedView());
            }
        });

        store.on('load', function() {
            // bug fix
            var selected = grid.getSelectionModel().selected;
            selected.keys.forEach(function(key) {
                selected.replace(key, store.getById(key));
            });

            var record = me.getSelectedRecord();
            if (record) {
                var details = me.down('#details');
                details.removeAll();
                details.add(me.createLanguageDetailsView(record.get('locale')));
            }
        });
    }
});
