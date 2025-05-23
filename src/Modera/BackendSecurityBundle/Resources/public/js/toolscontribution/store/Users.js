/**
 * @copyright 2014 Modera Foundation
 */
Ext.define('Modera.backend.security.toolscontribution.store.Users', {
    extend: 'Ext.data.DirectStore',

    constructor: function(config) {
        var defaults = {
            remoteSort: true,
            remoteFilter: true,
            fields: [
                'id', 'username' , 'email', 'meta',
                'personalId', 'firstName', 'lastName', 'middleName',
                'isActive', 'state', 'lastLogin', 'groups', 'permissions'
            ],
            proxy: {
                type: 'direct',
                directFn: Actions.ModeraBackendSecurity_Users.list,
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
                { property: 'id', direction: 'ASC' }
            ],
            autoLoad: true
        };
        this.config = Ext.apply(defaults, config || {});
        this.callParent([this.config]);
    },

    applyFilters: function(arr) {
        Ext.apply(this.proxy.extraParams, {
            filter: Ext.Array.merge(this.permanentFilters || [], arr)
        });
        this.load();
    }
});