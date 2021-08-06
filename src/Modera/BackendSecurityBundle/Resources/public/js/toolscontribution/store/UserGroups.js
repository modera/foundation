/**
 * @author Sergei Vizel <sergei.vizel@modera.org>
 */
Ext.define('Modera.backend.security.toolscontribution.store.UserGroups', {
    extend: 'Ext.data.DirectStore',

    // override
    constructor: function(config) {
        var defaults = {
            remoteSort: true,
            remoteFilter: true,
            fields: [
                'id', 'name'
            ],
            remoteFilter: true,
            proxy: {
                type: 'direct',
                directFn: Actions.ModeraBackendSecurity_Groups.list,
                pageParam: false,
                startParam: false,
                limitParam: false,
                extraParams: {
                    hydration: {
                        profile: 'compact-list'
                    }
                },
                reader: {
                    root: 'items'
                }
            },
            sorters: [
                { property: 'id', direction: 'ASC' }
            ],
            autoLoad: false
        };
        this.config = Ext.apply(defaults, config || {});
        this.callParent([this.config]);
    },

    /**
     * @param {String} userId
     */
    filterByUser: function(userId, exp) {
        this.filters.clear();
        this.filter({ property: 'users', value: (exp || 'in') + ':' + userId });
    }
});