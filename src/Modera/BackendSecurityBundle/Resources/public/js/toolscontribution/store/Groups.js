/**
 * @author Sergei Lissovski <sergei.lissovski@modera.org>
 */
Ext.define('Modera.backend.security.toolscontribution.store.Groups', {
    extend: 'Ext.data.DirectStore',

    constructor: function(config) {
        var defaults = {
            remoteSort: true,
            remoteFilter: true,
            fields: [
                'id', 'name', 'refName', 'usersCount'
            ],
            proxy: {
                type: 'direct',
                directFn: Actions.ModeraBackendSecurity_Groups.list,
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
                { property: 'id', direction: 'ASC' }
            ],
            autoLoad: true
        };
        this.config = Ext.apply(defaults, config || {});
        this.callParent([this.config]);
    }
});