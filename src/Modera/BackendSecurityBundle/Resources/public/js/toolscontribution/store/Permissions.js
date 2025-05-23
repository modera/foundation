/**
 * @copyright 2014 Modera Foundation
 */
Ext.define('Modera.backend.security.toolscontribution.store.Permissions', {
    extend: 'Ext.data.DirectStore',

    constructor: function(config) {
        var defaults = {
            remoteSort: true,
            remoteFilter: true,
            fields: [
                'id', 'name', 'category', 'users', 'groups'
            ],
            groupers: [
                {
                    property: 'category.position',
                    direction: 'DESC',
                    getGroupString: function(record) {
                        return record.get('category')['name'];
                    }
                },
                {
                    property: 'category.id',
                    direction: 'ASC'
                }
            ],
            proxy: {
                type: 'direct',
                directFn: Actions.ModeraBackendSecurity_Permissions.list,
                pageParam: false,
                startParam: false,
                limitParam: false,
                extraParams: {
                    hydration: {
                        profile: 'list'
                    }
                },
                reader: {
                    root: 'items'
                }
            },
            sorters: [
                { property: 'position', direction: 'DESC' },
                { property: 'id', direction: 'ASC' }
            ],
            autoLoad: true
        };
        this.config = Ext.apply(defaults, config || {});
        this.callParent([this.config]);
    },

    filterByUser: function(userId, exp) {
        this.permanentFilters = [
            { property: 'users', value: (exp || 'in') + ':' + userId }
        ];
        this.applyFilters([]);
    },

    filterByGroup: function(groupId, exp) {
        this.permanentFilters = [
            { property: 'groups', value: (exp || 'in') + ':' + groupId }
        ];
        this.applyFilters([]);
    },

    applyFilters: function(arr) {
        Ext.apply(this.proxy.extraParams, {
            filter: Ext.Array.merge(this.permanentFilters || [], arr)
        });
        this.load();
    }
});