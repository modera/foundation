/**
 * @since 2.56.0
 * @internal
 *
 * @author Sergei Lissovski <sergei.lissovski@modera.org>
 */
Ext.define('Modera.backend.security.passwordrotation.PasswordRotationPlugin', {
    extend: 'MF.runtime.extensibility.AbstractPlugin',

    requires: [
        'MF.Util',
        'Modera.backend.security.toolscontribution.view.user.PasswordWindow'
    ],

    constructor: function(workbench) {
        this.workbench = workbench;

        this.callParent(arguments);
    },

    getId: function() { // override
        return 'password-rotation';
    },

    bootstrap: function(callback) { // override
        var me = this;

        Actions.ModeraBackendSecurity_Users.isPasswordRotationNeeded({}, function(response) {
            if (response.success) {
                if (response.result.isRotationNeeded) {
                    me.workbench.getService('config_provider').getConfig(function(config) {
                        var intervalHandle = setInterval(function() {
                            var am = me.workbench.getActivitiesManager();
                            var hasRuntimeBeenBootstrapped = !!am;
                            if (hasRuntimeBeenBootstrapped) {
                                var activity = am.getActivity('edit-password');
                                var cx = me.workbench.getRootExecutionContext();
                                if (!cx.hasActivity(activity)) {
                                    me.workbench.launchActivity('edit-password', {
                                        id: config['userProfile']['id'],
                                        rotation: true
                                    });
                                }

                                clearInterval(intervalHandle);
                            }
                        }, 100);

                    });
                }
            }
        });

        callback();
    }
});