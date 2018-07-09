/**
 * @author Sergei Vizel <sergei.vizel@modera.org>
 */
Ext.define('Modera.backend.languages.runtime.language.EditWindowActivity', {
    extend: 'MF.activation.activities.AbstractActivity',

    requires: [
        'Modera.backend.languages.view.language.EditWindow'
    ],

    // override
    getId: function() {
        return 'editlanguage';
    },

    getSecurityConfig: function() {
        return {
            role: 'ROLE_ACCESS_BACKEND_SYSTEM_SETTINGS'
        }
    },

    // override
    doCreateUi: function(params, callback) {
        var requestParams = {
            filter: [
                { property: 'id', value: 'eq:' + params.id }
            ],
            hydration: {
                profile: 'list'
            }
        };

        Actions.ModeraBackendLanguages_Languages.get(requestParams, function(response) {
            var window = Ext.create('Modera.backend.languages.view.language.EditWindow');
            window.loadData(response.result);
            callback(window);
        });
    },

    // protected
    attachListeners: function(ui) {
        ui.on('saveandclose', function(window) {
            var form = window.down('form').getForm();
            if (form.isValid()) {
                var values = form.getValues();
                Actions.ModeraBackendLanguages_Languages.update({ record: values }, function(response) {
                    if (response.success) {
                        window.close();
                    } else {
                        window.showErrors(response);
                    }
                });
            }
        })
    }
});