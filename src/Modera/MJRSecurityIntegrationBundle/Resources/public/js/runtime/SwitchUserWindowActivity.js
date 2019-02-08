/**
 * @author Sergei Vizel <sergei.vizel@modera.org>
 */
Ext.define('Modera.mjrsecurityintegration.runtime.SwitchUserWindowActivity', {
    extend: 'MF.activation.activities.AbstractActivity',

    requires: [
        'Modera.mjrsecurityintegration.view.SwitchUserWindow'
    ],

    // override
    getId: function() {
        return 'switch-user';
    },

    // override
    doCreateUi: function(params, callback) {
        var me = this;

        var window = Ext.create('Modera.mjrsecurityintegration.view.SwitchUserWindow', {});

        callback(window);
    },

    // protected
    attachListeners: function(ui) {
        var me = this;

        var decode = function(responseText) {
            var resp;
            try {
                resp = Ext.decode(responseText);
            } catch(e) {
                resp = {
                    success: false,
                    message: 'Unrecognized error.'
                };
            }
            return resp;
        };

        ui.on('switchuser', function(window, username) {
            me.workbench.getService('config_provider').getConfig(function(config) {
                window.close();
                Ext.Ajax.request({
                    url: config['switchUserUrl'] + username,
                    success: function(response) {
                        var resp = decode(response.responseText);
                        if (resp['success'] && resp['profile']['username'] !== config['userProfile']['username']) {
                            location.replace('//' + location.hostname + location.pathname);
                        }
                    },
                    failure: function(response) {
                        var resp = decode(response.responseText);
                        console.error(resp);
                    }
                });
            });
        });
    }
});
