/**
 * @author Sergei Lissovski <sergei.lissovski@modera.org>
 */
Ext.define('Modera.backend.security.toolscontribution.runtime.group.ListActivity', {
    extend: 'MF.activation.activities.AbstractActivity',

    requires: [
        'Modera.backend.security.toolscontribution.view.group.Overview'
    ],

    // override
    getId: function() {
        return 'groups';
    },

    // override
    init: function(executionContext) {
        this.callParent(arguments);

        executionContext.getApplication().loadController('Modera.backend.security.toolscontribution.controller.Groups');
    },

    // override
    doCreateUi: function(params, callback) {
        var me = this;

        me.workbench.getService('config_provider').getConfig(function(config) {
            var securityConfig = config['modera_backend_security'] || {};

            var grid = Ext.create('Modera.backend.security.toolscontribution.view.group.Overview', {
                hideDeleteUserFunctionality: securityConfig['hideDeleteUserFunctionality']
            });

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

        ui.on('creategroup', function(sourceComponent) {
            me.fireEvent('handleaction', 'new-group', sourceComponent);
        });
        ui.on('deletegroup', function(sourceComponent, record) {
            me.fireEvent('handleaction', 'delete-group', sourceComponent, { id: record.get('id') });
        });
        ui.on('editgroup', function(sourceComponent, record) {
            me.fireEvent('handleaction', 'edit-group', sourceComponent, { id: record.get('id') });
        });

        ui.on('editrecord', function(sourceComponent, params) {
            me.fireEvent('handleaction', 'edit-user', sourceComponent, params);
        });
        ui.on('deleterecord', function(sourceComponent, params) {
            me.fireEvent('handleaction', 'delete-user', sourceComponent, params);
        });
        ui.on('editgroups', function(sourceComponent, params) {
            me.fireEvent('handleaction', 'edit-groups', sourceComponent, params);
        });
    }
});