/**
 * @author Sergei Vizel <sergei.vizel@modera.org>
 */
Ext.define('Modera.backend.languages.runtime.language.DeleteWindowActivity', {
    extend: 'MF.activation.activities.BasicDeleteRecordWindowActivity',

    requires: [
        'MFC.window.DeleteRecordConfirmationWindow'
    ],

    getSecurityConfig: function() {
        return {
            role: 'ROLE_ACCESS_BACKEND_SYSTEM_SETTINGS'
        }
    },

    // override
    getHydrationProfileId: function() {
        return 'remove';
    },

    // override
    constructor: function(config) {
        var defaults = {
            id: 'removeconfiguration',
            uiClass: 'Modera.backend.languages.view.language.DeleteWindow',
            directClass: Actions.ModeraBackendLanguages_Languages,
            responseRecordNameKey: 'key'
        };

        this.callParent([Ext.apply(defaults, config || {})]);
    }
});