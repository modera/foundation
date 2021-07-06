/**
 * @author Sergei Vizel <sergei.vizel@modera.org>
 */
Ext.define('Modera.backend.security.toolscontribution.store.Permissions', {
    extend: 'Ext.data.DirectStore',

    constructor: function() {
        this.config = {
            fields: [
                'id', 'name' , 'category', 'users', 'groups'
            ],
            groupField: 'category',
            proxy: {
                type: 'direct',
                directFn: Actions.ModeraBackendSecurity_Permissions.list,
                // MPFE-966, removing "limit" parameter from query, so server would return all available permissions:
                limitParam: null,
                extraParams: {
                    hydration: {
                        profile: 'list'
                    }
                },
                reader: {
                    root: 'items'
                }
            },
            autoLoad: true
        };
        this.callParent([this.config]);
    },

    filterByUser: function(userId, exp) {
        this.filters.clear();
        this.filter({ property: 'users', value: (exp || 'in') + ':' + userId });
    },

    filterByGroup: function(groupId, exp) {
        this.filters.clear();
        this.filter({ property: 'groups', value: (exp || 'in') + ':' + groupId });
    }
});