/**
 * @author Sergei Lissovski <sergei.lissovski@modera.org>
 */
Ext.define('Modera.backend.security.toolscontribution.runtime.user.ListActivity', {
    extend: 'MF.activation.activities.AbstractActivity',

    requires: [
        'Modera.backend.security.toolscontribution.view.user.List'
    ],

    // override
    getId: function() {
        return 'users';
    },

    getSecurityConfig: function() {
        return {
            role: 'ROLE_ACCESS_BACKEND_TOOLS_SECURITY_SECTION'
        }
    },

    // override
    doCreateUi: function(params, callback) {
        var me = this;
        me.workbench.getService('config_provider').getConfig(function(config) {
            var securityConfig = config['modera_backend_security'] || {};
            var listConfig = {
                hideDeleteUserFunctionality: securityConfig['hideDeleteUserFunctionality']
            };
            var grid = Ext.create('Modera.backend.security.toolscontribution.view.user.List', listConfig);

            callback(grid);
        });
    },

    // override
    attachListeners: function(ui) {
        var me = this;

        ui.on('enableprofile', function(sourceComponent, params) {
            Actions.ModeraBackendSecurity_Users.update({
                record: {
                    id: params['id'],
                    active: true
                }
            }, function(response) {
                //
            });
        });
        ui.on('disableprofile', function(sourceComponent, params) {
            Actions.ModeraBackendSecurity_Users.update({
                record: {
                    id: params['id'],
                    active: false
                }
            }, function(response) {
                //
            });
        });
    },

    // override
    attachContractListeners: function(ui) {
        var me = this;

        ui.on('newrecord', function(sourceComponent) {
            me.fireEvent('handleaction', 'new-user', sourceComponent);
        });
        ui.on('deleterecord', function(sourceComponent, params) {
            me.fireEvent('handleaction', 'delete-user', sourceComponent, params);
        });
        ui.on('editpermissions', function(sourceComponent, params) {
            me.fireEvent('handleaction', 'edit-permissions', sourceComponent, params);
        });
        ui.on('editgroups', function(sourceComponent, params) {
            me.fireEvent('handleaction', 'edit-groups', sourceComponent, params);
        });

        var intentMgr = this.workbench.getService('intent_manager');

        ui.on('editrecord', function(panel, data) {
            intentMgr.dispatch({
                name: 'edit-user',
                params: data
            }, Ext.emptyFn, ['use_first_handler']);
        });

        ui.on('editpassword', function(panel, data) {
            intentMgr.dispatch({
                name: 'edit-password',
                params: data
            }, Ext.emptyFn, ['use_first_handler']);
        });
    }
});