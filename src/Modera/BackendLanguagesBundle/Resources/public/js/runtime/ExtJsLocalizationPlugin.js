/**
 * @copyright 2014 Modera Foundation
 */
Ext.define('Modera.backend.languages.runtime.ExtJsLocalizationPlugin', {
    extend: 'MF.runtime.extensibility.AbstractPlugin',

    requires: [
        'MF.theme.Header'
    ],

    // override
    constructor: function(config) {
        this.callParent(arguments);
        this.config = config;
    },

    // override
    getId: function() {
        return 'extjs_localization_runtime_plugin';
    },

    // override
    bootstrap: function(cb) {
        var me = this;
        var workbench = me.application.getContainer().get('workbench');
        workbench.getService('config_provider').getConfig(function(config) {
            me.loadScripts(Ext.Array.map(me.config['urls'], function(value) {
                return value.replace('__LOCALE__', config['modera_backend_languages']['locale']);
            }), function() {
                var workbenchPanel = Ext.ComponentQuery.query('component[runtimerole=workbench]')[0];
                if (workbenchPanel) {
                    var header = workbenchPanel.down('#header');
                    if (header) {
                        workbenchPanel.__configureUi = workbenchPanel.configureUi;
                        workbenchPanel.configureUi = function(authenticationResult, runtimeConfig, callback) {
                            console.log(me.$className + ': header re-render');
                            workbenchPanel.remove(header);
                            header = workbenchPanel.add({
                                itemId: header.itemId,
                                rtl: 'rtl' === config['modera_backend_languages']['direction'],
                                xtype: 'mf-theme-header',
                                region: header.region
                            });
                            header.on('logout', function() {
                                workbenchPanel.fireEvent('logout', header);
                            });
                            workbenchPanel.__configureUi(authenticationResult, runtimeConfig, callback);
                        };
                        workbenchPanel.sectionChanged = function(owningSectionName, params, sectionName) {
                            header.highlightMenuItem(owningSectionName);
                        }

                        // var logoutBtn = header.down('button[tid=logoutBtn]');
                        // if (logoutBtn) {
                        //     logoutBtn.setText(header.logoutText);
                        // }
                    }
                }

                cb();
            });
        });
    },

    // private
    loadScripts: function(urls, fn) {
        var me = this;
        var url = urls.shift();
        Ext.Loader.loadScript({
            url: url,
            onLoad: function() {
                if (urls.length > 0) {
                    me.loadScripts(urls, fn);
                } else {
                    fn();
                }
            },
            onError: function() {
                console.error('Url "' + url + '" not loaded!');

                var re = /ext-lang-(\D{2})(_.*)\.js/i
                if (url.match(re)) {
                    var tryUrl = url.replace(re, 'ext-lang-$1.js');
                    urls.unshift(tryUrl);
                    console.info('Try to load "' + tryUrl + '"');
                    me.loadScripts(urls, fn);
                } else {
                    if (urls.length > 0) {
                        me.loadScripts(urls, fn);
                    } else {
                        fn();
                    }
                }
            }
        });
    }
});