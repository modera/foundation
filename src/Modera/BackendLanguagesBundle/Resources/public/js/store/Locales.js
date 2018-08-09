/**
 * @author Sergei Vizel <sergei.vizel@modera.org>
 */
Ext.define('Modera.backend.languages.store.Locales', {
    extend: 'Ext.data.DirectStore',

    constructor: function(config) {
        var defaults = {
            fields: [
                'id', 'name'
            ],
            proxy: {
                type: 'direct',
                directFn: Actions.ModeraBackendLanguages_Locales.list,
                pageParam: false,
                startParam: false,
                limitParam: false,
                extraParams: Ext.apply({
                    hydration: {
                        profile: 'list'
                    }
                }, config.extraParams || {}),
                reader: {
                    root: 'items'
                }
            },
            autoLoad: false
        };
        this.config = Ext.apply(defaults, config || {});
        this.callParent([this.config]);
    }
});
