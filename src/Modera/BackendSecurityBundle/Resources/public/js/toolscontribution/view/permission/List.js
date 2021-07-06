/**
 * @author Sergei Vizel <sergei.vizel@modera.org>
 */
Ext.define('Modera.backend.security.toolscontribution.view.permission.List', {
    extend: 'Ext.grid.Panel',
    alias: 'widget.modera-backend-security-permission-list',

    requires: [
        'Modera.backend.security.toolscontribution.store.Permissions'
    ],

    // l10n
    titleText: 'Permissions',
    inheritedGroupText: 'Inherited',

    // override
    constructor: function(config) {
        var me = this;

        var defaults = {
            tid: 'permissionsOverviewView',
            frame: true,
            rounded: true,
            columnLines: true,
            emptyCls: 'mfc-grid-empty-text',
            features: [{
                ftype:'grouping',
                groupHeaderTpl: '{name:htmlEncode}'
            }],
            viewConfig: {
                loadMask: false,
                markDirty: false
            },
            monitorModel: {
                name: 'modera.security_bundle.group',
                handler: function() {
                    config['groupsStore'].load(function() {
                        me.reconfigure(config['store'], me.generateColumns(
                            config['groupsStore'], config['groupsType'], config['firstColumnFlex']
                        ));
                    });
                }
            },
            columns: me.generateColumns(
                config['groupsStore'], config['groupsType'], config['firstColumnFlex']
            )
        };

        me.config = Ext.apply(defaults, config || {});
        me.callParent([me.config]);

        me.addEvents(
            /**
             * @event permissionchange
             * @param {Modera.backend.security.toolscontribution.view.permission.List} me
             * @param {Object} params
             */
            'permissionchange',

            /**
             * @event groupchange
             * @param {Modera.backend.security.toolscontribution.view.permission.List} me
             * @param {Object} params
             */
            'groupchange'
        );

        me.assignListeners();
    },

    // private
    generateColumns: function(groupsStore, groupsType, firstColumnFlex) {
        var me = this;
        var columns = [
            {
                dataIndex: 'name',
                flex: firstColumnFlex || 4,
                sortable: false,
                hideable: false,
                closable: false,
                renderer: 'htmlEncode'
            }
        ];
        groupsStore.each(function(group) {
            var id = group.get('id');
            var name = Ext.util.Format.htmlEncode(group.get('name'));
            if (!id) {
                name = me.inheritedGroupText;
            }

            columns.push(me.getCheckerColumnConfig({
                groupId: id,
                text: name,
                dataIndex: groupsType
            }));
        });

        return columns;
    },

    // private
    assignListeners: function() {
        var me = this;

        me.on('beforeselect', function(sm, record, index) {
            return false;
        });

        me.on('cellclick', function(view, td, cellIndex, record, tr, rowIndex, e) {
            if (!me.config.hasAccess) {
                return false;
            }

            var columns = me.columnManager.getColumns();

            var column = columns[cellIndex];
            if (column && column['groupId']) {
                me.toggleChecker(tr, column, record);
            }
        });
    },

    // private
    getCheckerColumnConfig: function(config) {
        var me = this;

        return Ext.apply({
            flex: 1,
            clickTargetName: 'el',
            sortable: false,
            draggable: false,
            resizable: false,
            hideable: false,
            menuDisabled: true,
            align: 'center',
            tdCls: Ext.baseCSSPrefix + 'grid-cell-checkcolumn',
            innerCls: Ext.baseCSSPrefix + 'grid-cell-inner-checkcolumn',
            renderer : function(values, meta, record) {
                var cssPrefix = Ext.baseCSSPrefix;
                var cls = [cssPrefix + 'grid-checkcolumn', 'group-' + config['groupId']];

                meta.style = 'cursor:pointer;';

                if (this.disabled || !me.config.hasAccess || !config['groupId']) {
                    meta.tdCls += ' ' + this.disabledCls;
                }

                var checked = values.indexOf(config['groupId']) !== -1;
                if (checked) {
                    cls.push(cssPrefix + 'grid-checkcolumn-checked');
                }
                return '<img class="' + cls.join(' ') + '" src="' + Ext.BLANK_IMAGE_URL + '"/>';
            },
            locked: false
        }, config);
    },

    // private
    toggleChecker: function(node, column, record) {
        var me = this;
        var view = me.getView();
        var cssPrefix = Ext.baseCSSPrefix;

        var checkbox = Ext.fly(node).down('.' + cssPrefix + 'grid-checkcolumn.group-' + column['groupId']);
        if (checkbox.hasCls(cssPrefix + 'grid-checkcolumn-checked')) {
            checkbox.removeCls(cssPrefix + 'grid-checkcolumn-checked');
        } else {
            checkbox.addCls(cssPrefix + 'grid-checkcolumn-checked');
        }

        var groupIds = [];
        var checkboxes = Ext.fly(node).query('.' + cssPrefix + 'grid-checkcolumn.' + cssPrefix + 'grid-checkcolumn-checked');
        Ext.each(checkboxes, function(checkbox) {
            Ext.each(checkbox.className.split(' '), function(cls) {
                if (cls.indexOf('group-') !== -1) {
                    groupIds.push(parseInt(cls.replace('group-', '')));
                }
            });
        });

        record.set(me.config['groupsType'], groupIds);

        var params = { id: record.get('id') };
        params[me.config['groupsType']] = groupIds;
        me.fireEvent('permissionchange', me, params);

        var permissions = [];
        me.getStore().each(function(permission) {
            var values = permission.get(me.config['groupsType']);
            if (values.indexOf(column['groupId']) !== -1) {
                permissions.push(permission.get('id'));
            }
        });

        me.fireEvent('groupchange', me, {
            id: column['groupId'],
            permissions: permissions
        });
    }
});