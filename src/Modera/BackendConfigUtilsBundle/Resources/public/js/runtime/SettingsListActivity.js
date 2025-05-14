/**
 * Provides a simplistic way how to edit configuration entries of certain category. This activity will check
 * "category" activation parameter to decide what category to read configuration entries from.
 *
 * @copyright 2014 Modera Foundation
 */
Ext.define('Modera.backend.configutils.runtime.SettingsListActivity', {
    extend: 'MF.activation.activities.AbstractActivity',

    requires: [
        'Modera.backend.configutils.view.PropertiesGrid'
    ],

    getId: function() { // override
        return 'general-config';
    },

    doCreateUi: function(params, callback) { // override
        var ui = Ext.create('Modera.backend.configutils.view.PropertiesGrid', {
            monitorModel: 'modera.config_bundle.configuration_entry',
            category: params.category || 'general'
        });

        callback(ui);
    }
});