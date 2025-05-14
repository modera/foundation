/**
 * @copyright 2014 Modera Foundation
 */
Ext.define('Modera.backend.tools.activitylog.store.Activities', {
    extend: 'Ext.data.DirectStore',

    // override
    constructor: function() {
        this.config = {
            remoteSort: true,
            remoteFilter: true,
            fields: [
                'id', 'author', 'type', 'level', 'message', 'createdAt', 'meta'
            ],
            proxy: {
                type: 'direct',
                directFn: Actions.ModeraBackendToolsActivityLog_Default.list,
                reader: {
                    root: 'items'
                }
            }
        };
        this.callParent([this.config]);
    }
});