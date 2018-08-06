/**
 * @author Sergei Vizel <sergei.vizel@modera.org>
 */
Ext.define('Modera.backend.languages.runtime.ListActivity', {
    extend: 'MF.activation.activities.AbstractActivity',

    requires: [
        'Modera.backend.languages.view.List'
    ],

    // l10n
    headerTitleText: 'Languages',

    // override
    isHomeActivity: function() {
        return true;
    },

    // override
    getId: function() {
        return 'list';
    },

    getSecurityConfig: function() {
        return {
            role: 'ROLE_ACCESS_BACKEND_SYSTEM_SETTINGS'
        }
    },

    // used by Modera.backend.salespad.runtime.BreadcrumbsManagingActivationInterceptor
    configureHeaderUi: function(callback) {
        this.configureHeaderUiCb = callback;

        callback(this.headerUiConfig);
    },

    // override
    doInit: function(callback) {
        var me = this;

        me.headerUiConfig = {
            breadcrumbs: [ me.headerTitleText ]
        };

        callback(this);
    },

    // override
    doCreateUi: function(params, callback) {
        var ui = Ext.create('Modera.backend.languages.view.List', {});
        callback(ui);
    },

    // override
    attachContractListeners: function(ui) {
        var me = this;

        ui.on('newrecord', function(sourceComponent, params) {
            me.handleAction('newlanguage', sourceComponent, params);
        });

        ui.on('editrecord', function(sourceComponent, params) {
            me.handleAction('editlanguage', sourceComponent, params);
        });

        ui.on('deleterecord', function(sourceComponent, params) {
            me.handleAction('removelanguage', sourceComponent, params);
        });
    }
});