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

        me.workbench.getService('config_provider').getConfig(function(config) {
            var window = Ext.create('Modera.mjrsecurityintegration.view.SwitchUserWindow', {
                switchUserListAction: config['switchUserListAction']
            });

            callback(window);
        });
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
                    url: config['switchUserUrl'].replace('__username__', username),
                    success: function(response) {
                        var resp = decode(response.responseText);
                        if (resp['success'] && resp['profile']['username'] !== config['userProfile']['username']) {
                            location.replace(me.resolveRedirectUrl());
                        }
                    },
                    failure: function(response) {
                        var resp = decode(response.responseText);
                        console.error(resp);
                    }
                });
            });
        });
    },

    // protected
    resolveRedirectUrl: function() {
        return '//' + location.host + location.pathname + location.search;
    }
});
