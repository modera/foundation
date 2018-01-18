/**
 * @author Alex Rudakov <alexandr.rudakov@modera.org>
 * @copyright 2014 Modera Foundation
 */
Ext.define('Modera.backend.dashboard.view.DashboardSettingsWindow', {
    extend: 'MFC.window.ModalWindow',
    alias: 'widget.modera-backend-dashboard-dashboardettingswindow',

    requires: [
        'MF.Util',
        'Ext.String',
        'Ext.util.Format'
    ],

    // l10n
    titleText: 'Landing view settings for {0}',
    titleForSeveralUsersText: 'Landing view settings',
    dashboardNameColumnText: 'Dashboard name',
    hasAccessColumnText: 'Has access',
    isDefaultColumnText: 'Is default',
    updateBtnText: 'Update',
    defaultLandingViewText: 'Default landing view',

    // override
    constructor: function(config) {
        var me = this;

        // 'data' object must have two fields: "id", "dashboards"
        MF.Util.validateRequiredConfigParams(this, config, ['data']);

        var landingSection = config.data.landingSection || 'dashboard';

        var defaults = {
            title: Ext.String.format(this.titleText, Ext.util.Format.htmlEncode(config.data.title)),
            width: 500,
            height: 400,
            layout: 'fit',
            items: [
                {
                    layout: {
                        type: 'vbox',
                        align: 'stretch',
                        pack: 'start'
                    },
                    items: [
                        {
                            itemId: 'landingSection',
                            xtype: 'combo',
                            editable: false,
                            labelWidth: 150,
                            fieldLabel: me.defaultLandingViewText,
                            store: Ext.create('Ext.data.Store', {
                                fields: ['id', 'label'],
                                data: config.data.sections || []
                            }),
                            queryMode: 'local',
                            displayField: 'label',
                            valueField: 'id',
                            value: landingSection,
                            listeners: {
                                change: function(combo, newValue, oldValue) {
                                    if ('dashboard' === newValue) {
                                        me.down('#dashboards').enable();
                                    } else {
                                        me.down('#dashboards').disable();
                                    }
                                }
                            }
                        },
                        {
                            flex: 1,
                            itemId: 'dashboards',
                            disabled: 'dashboard' !== landingSection,
                            xtype: 'grid',
                            border: true,
                            columns: [
                                {
                                    header: this.dashboardNameColumnText,
                                    dataIndex: 'name',
                                    flex: 1,
                                    renderer: 'htmlEncode'
                                },
                                {
                                    xtype: 'checkcolumn',
                                    header: this.hasAccessColumnText,
                                    dataIndex: 'hasAccess',
                                    listeners: {
                                        beforecheckchange: {
                                            fn: this.onHasAccessColumnBeforeCheckChange,
                                            scope: this
                                        }
                                    }
                                },
                                {
                                    xtype: 'checkcolumn',
                                    header: this.isDefaultColumnText,
                                    dataIndex: 'isDefault',
                                    listeners: {
                                        checkchange: {
                                            fn: this.onIsDefaultColumnCheckChange,
                                            scope: this
                                        }
                                    },
                                    scope: this
                                }
                            ],
                            store: Ext.create('Ext.data.Store', {
                                proxy: {
                                    type: 'memory'
                                },
                                fields: ['id', 'name', 'hasAccess', 'isDefault'],
                                data: config.data.dashboardSettings
                            })
                        }
                    ]
                }
            ],
            actions: [
                '->',
                {
                    text: this.updateBtnText,
                    scale: 'medium',
                    itemId: 'updateBtn',
                    iconCls: 'mfc-icon-apply-24'
                }
            ]
        };

        this.callParent([Ext.apply(defaults, config || {})]);

        this.addEvents(
            /**
             * @event update
             * @param {Modera.backend.dashboard.view.DashboardSettingsWindow} me
             * @param {Object[]} data  See assignListeners() method for the signature of this object
             */
            'update'
        );

        this.assignListeners();
    },

    // private
    assignListeners: function() {
        var me = this;

        this.down('#updateBtn').on('click', function() {
            var dashboards = [];
            me.down('grid').getStore().each(function(iteratedRecord) {
                dashboards.push(iteratedRecord.data);
            });

            me.fireEvent('update', me, {
                landingSection: me.down('#landingSection').getValue(),
                dashboards: dashboards,
                id: me.config.data.id
            });
        });
    },

    // private
    onHasAccessColumnBeforeCheckChange: function(columnHeader, rowIndex) {
        var clickedRowRecord = this.down('grid').getStore().getAt(rowIndex);

        // preventing from uncheking "has access" checkbox if "is default" is checked for this row
        if (clickedRowRecord.get('isDefault') == true) {
            return false;
        }
    },

    // private
    onIsDefaultColumnCheckChange: function(columnHeader, rowIndex, checked) {
        var store = this.down('grid').getStore();

        var clickedRowRecord = store.getAt(rowIndex);
        if (checked) {
            clickedRowRecord.set('hasAccess', true);

            store.each(function(iteratedRecord) {
                // there can be only one column wit active "isDefault"
                if (iteratedRecord.get('id') != clickedRowRecord.get('id')) {
                    iteratedRecord.set('isDefault', false);
                }
            });
        }
    }
});