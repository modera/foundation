/**
 * @author Sergei Vizel <sergei.vizel@modera.org>
 */
Ext.define('Modera.backend.languages.runtime.language.NewWindowActivity', {
    extend: 'MF.activation.activities.AbstractActivity',

    requires: [
        'Modera.backend.languages.view.language.NewWindow'
    ],

    // override
    getId: function() {
        return 'newlanguage';
    },

    getSecurityConfig: function() {
        return {
            role: 'ROLE_ACCESS_BACKEND_SYSTEM_SETTINGS'
        }
    },

    // override
    doCreateUi: function(params, callback) {
        var requestParams = {
            hydration: {
                profile: 'list'
            }
        };
        Actions.ModeraBackendLanguages_Languages.list(requestParams, function(response) {
            var ignore = Ext.Array.map(response.items, function(item) {
                return item['locale'];
            });

            var window = Ext.create('Modera.backend.languages.view.language.NewWindow', {
                dto: {
                    ignore: ignore
                }
            });

            callback(window);
        });
    },

    // override
    attachListeners: function(window) {
        window.on('saveandclose', function() {
            var form = window.down('form').getForm();
            if (form.isValid()) {
                var values = form.getValues();
                if (values['isDefault']) {
                    values['isEnabled'] = true;
                }
                Actions.ModeraBackendLanguages_Languages.create({ record: values }, function(response) {
                    if (response.success) {
                        window.close();
                    } else {
                        window.showErrors(response);
                    }
                });
            }
        });
    }
});