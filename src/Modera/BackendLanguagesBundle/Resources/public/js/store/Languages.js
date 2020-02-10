/**
 * @author Sergei Vizel <sergei.vizel@modera.org>
 */
Ext.define('Modera.backend.languages.store.Languages', {
    extend: 'Ext.data.DirectStore',

    constructor: function(config) {
        var defaults = {
            fields: [
                'id', 'name', 'locale', 'isEnabled', 'isDefault'
            ],
            proxy: {
                type: 'direct',
                directFn: Actions.ModeraBackendLanguages_Languages.list,
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
            autoLoad: false
        };
        this.config = Ext.apply(defaults, config || {});
        this.callParent([this.config]);
    }
});
