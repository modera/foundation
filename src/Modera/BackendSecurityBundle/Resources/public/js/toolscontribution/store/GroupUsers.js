/**
 * @author Sergei Lissovski <sergei.lissovski@modera.org>
 */
Ext.define('Modera.backend.security.toolscontribution.store.GroupUsers', {
    extend: 'Ext.data.DirectStore',

    // override
    constructor: function(config) {
        var defaults = {
            remoteSort: true,
            remoteFilter: true,
            fields: [
                'id', 'username', 'fullname', 'isActive', 'state'
            ],
            proxy: {
                type: 'direct',
                directFn: Actions.ModeraBackendSecurity_Users.list,
                extraParams: {
                    hydration: {
                        profile: 'modera-backend-security-group-groupusers'
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
     * @param {String} groupId
     */
    filterByGroup: function(groupId) {
        this.filters.clear();
        this.filter({ property: 'groups', value: 'in:' + groupId });
    }
});