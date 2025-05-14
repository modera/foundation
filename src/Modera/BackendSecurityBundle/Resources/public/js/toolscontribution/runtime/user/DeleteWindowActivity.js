/**
 * @copyright 2014 Modera Foundation
 */
Ext.define('Modera.backend.security.toolscontribution.runtime.user.DeleteWindowActivity', {
    extend: 'MF.activation.activities.BasicDeleteRecordWindowActivity',

    requires: [
        'MFC.window.DeleteRecordConfirmationWindow'
    ],

    getSecurityConfig: function() {
        return {
            role: 'ROLE_MANAGE_USER_ACCOUNTS'
        }
    },

    // override
    constructor: function(config) {
        var defaults = {
            id: 'delete-user',
            uiClass: 'MFC.window.DeleteRecordConfirmationWindow',
            directClass: Actions.ModeraBackendSecurity_Users,
            responseRecordNameKey: 'username'
        };

        this.callParent([Ext.apply(defaults, config || {})]);
    }
});