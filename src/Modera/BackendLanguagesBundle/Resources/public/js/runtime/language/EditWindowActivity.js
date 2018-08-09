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
            hydration: {
                profile: 'list'
            }
        };
        Actions.ModeraBackendLanguages_Languages.list(requestParams, function(response) {
            var data = { id: params.id };
            var ignore = Ext.Array.map(Ext.Array.filter(response.items, function(item) {
                if (params.id == item['id']) {
                    data = item;
                    return false;
                }
                return true;
            }), function(item) {
                return item['locale'];
            });

            var window = Ext.create('Modera.backend.languages.view.language.EditWindow', {
                dto: {
                    ignore: ignore
                }
            });
            window.loadData(data);
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