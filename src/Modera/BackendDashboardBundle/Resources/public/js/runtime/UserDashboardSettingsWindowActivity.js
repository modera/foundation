/**
 * @author Alex Rudakov <alexandr.rudakov@modera.org>
 * @copyright 2014 Modera Foundation
 */
Ext.define('Modera.backend.dashboard.runtime.UserDashboardSettingsWindowActivity', {
    extend: 'MF.activation.activities.AbstractActivity',

    requires: [
        'Modera.backend.dashboard.view.DashboardSettingsWindow',
        'Modera.backend.dashboard.runtime.Section'
    ],

    // l10n
    dashboardSectionNameText: 'Dashboard',

    /**
     * @private
     * @property {boolean} isForSeveralUsers
     */

    /**
     * Only makes sense when settings are being modified for a single user.
     *
     * Will be used to determine if user has changed his landing section.
     *
     * @private
     * @property {String} originalLandingSectionName
     */

    // protected
    getEndpoint: function() {
        return Actions.ModeraBackendDashboard_UserSettings;
    },

    // override
    getId: function() {
        return 'user-dashboard-settings';
    },

    // protected
    getFilter: function(params) {
        return [
            { property: 'user.id', value: 'eq:' + params.id }
        ]
    },

    // override
    doCreateUi: function(params, onReadyCallback) {
        var me = this;

        this.isForSeveralUsers = Ext.isArray(params.id);

        var loadData = function(params, callback) {
            var query = {
                filter: me.getFilter(params),
                hydration: {
                    profile: 'main'
                }
            };
            me.getEndpoint().get(query, function(response) {
                if (response.success) {
                    callback(response);
                } else {
                    throw Ext.String.format(
                        '{0}: Unable to load landing-view-setting from server side, response: {1}',
                        me.$className, Ext.encode(response)
                    );
                }
            });
        };

        var createWindow = function(uiConfig) {
            me.workbench.getService('config_provider').getConfig(function(response) {
                // We are adding it here explicitly because it may not be sent by server side, see
                // Modera\BackendDashboardBundle\Contributions\MenuItemsProvider for more details
                uiConfig.data.sections = [
                    {
                        id: 'dashboard',
                        label: me.dashboardSectionNameText
                    }
                ];

                Ext.each(response['menuItems'], function(item) {
                    // Because it has already been added above
                    if ('dashboard' != item.id) {
                        uiConfig.data.sections.push({
                            id: item.id,
                            label: item.label
                        });
                    }
                });

                var window = Ext.create('Modera.backend.dashboard.view.DashboardSettingsWindow', uiConfig);

                onReadyCallback(window);
            });
        };

        if (me.isForSeveralUsers) {
            var ids = [];
            var dashboardSettings = null;
            Ext.each(params.id, function(id) {
                loadData({ id: id }, function(response) {
                    dashboardSettings = response.result['dashboardSettings'];
                    Ext.each(dashboardSettings, function(row) {
                        row['hasAccess'] = false;
                        row['isDefault'] = false;
                    });
                    ids.push(response.result['id']);

                    var isLastUser = params.id.length == ids.length;
                    if (isLastUser) {
                        var windowClass = Modera.backend.dashboard.view.DashboardSettingsWindow.prototype;
                        createWindow({
                            title: windowClass.titleForSeveralUsersText,
                            data: {
                                id: ids,
                                landingSection: 'dashboard',
                                dashboardSettings: dashboardSettings
                            }
                        });
                    }
                });
            });
        } else {
            loadData(params, function(response) {
                me.originalLandingSectionName = response.result.landingSection;

                createWindow({ data: response.result });
            });
        }
    },

    // private
    attachListeners: function(ui) {
        var me = this;

        ui.on('update', function(w, values) {
            w.disable();
            if (Ext.isArray(values['id'])) {
                var records = [];
                Ext.each(values['id'], function(id) {
                    var data = Ext.clone(values);
                    data['id'] = id;
                    records.push(data);
                });

                me.getEndpoint().batchUpdate({ records: records }, function(result) {
                    me.onLandingSectionOrDashboardSettingsUpdated(result, records);
                });

            } else {
                me.getEndpoint().update({ record: values }, function(result) {
                    me.onLandingSectionOrDashboardSettingsUpdated(result, records);
                });
            }
        });
    },

    // private
    onLandingSectionOrDashboardSettingsUpdated: function(serverResponse, records) {
        var me = this;

        var w = this.getUi(),
            ec = me.workbench.getRootExecutionContext();

        if (serverResponse.success) {
            var callback = function() {
                w.enable();
                w.close();

                if (!me.isForSeveralUsers) {
                    var newLandingSectionName = records[0].landingSection;

                    // See MPFE-981 for explanations

                    var usedToBeDashboardButChanged = 'dashboard' == me.originalLandingSectionName && 'dashboard' != newLandingSectionName,
                        changedToDashboard = me.originalLandingSectionName != 'dashboard' && 'dashboard' == newLandingSectionName;

                    if (usedToBeDashboardButChanged) {
                        // We need to refresh page because we need to hide "dashboard" section from the menu
                        // (accordingly to the business rules)

                        var isDashboardSectionCurrentlyLoaded = me.workbench.getCurrentSection() instanceof Modera.backend.dashboard.runtime.Section;

                        if (isDashboardSectionCurrentlyLoaded) {
                            // We need to a completely fresh start so MJR would figure out
                            // by itself what a new landing section should be
                            window.location.href = '';
                        } else {
                            // This will make it work properly in situations when a user opened a backend,
                            // a default section is loaded (but URL doesn't display it due to a bug MPFE-865)
                            ec.setSectionName(newLandingSectionName);

                            setTimeout(function() {
                                window.location.reload();
                            }, 500); // because it takes some time for a browser to update URL
                        }
                    } else if(changedToDashboard) {
                        // Need to refresh the page to show "dashboard" section

                        // Due to a bug MPFE-865, URL may not currently have currently loaded section name
                        ec.setSectionName(newLandingSectionName);

                        setTimeout(function() {
                            // Because we need to display dashboard section in the menu now, according to business
                            // rules
                            window.location.reload();
                        }, 500); // because it takes some time for a browser to update URL
                    } else {
                        ModeraFoundation.app.fireEvent(
                            'dashboardsettingsupdated', // internal event, do not rely on it!
                            records,
                            me.originalLandingSectionName,
                            newLandingSectionName
                        );
                    }
                }
            };

            var configProvider = me.workbench.getService('config_provider');
            if (configProvider) {
                var oldConfig = configProvider.cachedConfig;
                configProvider.cachedConfig = undefined;
                configProvider.getConfig(function (newConfig) {
                    Ext.applyIf(newConfig, oldConfig);

                    callback();
                });
            } else {
                callback();
            }
        } else {
            w.enable();

            throw Ext.String.format(
                '{0}: Error occurred when attempted to update landing view/dashboard sections, response: {1}',
                me.$className, Ext.encode(serverResponse)
            )
        }
    }
});