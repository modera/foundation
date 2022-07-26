/**
 * @author Sergei Vizel <sergei.vizel@modera.org>
 */
Ext.define('Modera.backend.security.toolscontribution.runtime.user.PermissionsWindowActivity', {
    extend: 'MF.activation.activities.AbstractActivity',

    requires: [
        'MFC.window.ModalWindow',
        'Modera.backend.security.toolscontribution.store.Permissions',
        'Modera.backend.security.toolscontribution.view.permission.List'
    ],

    // override
    getId: function() {
        return 'edit-permissions';
    },

    // override
    doCreateUi: function(params, callback) {
        var me = this;

        var loadMask = me.setLoading();

        me.workbench.getService('security_manager').isAllowed('ROLE_MANAGE_PERMISSIONS', function(isAllowed) {
            var requestParams = {
                filter: [
                    { property: 'id', value: 'eq:' + params.id }
                ],
                hydration: {
                    profile: 'compact-list'
                }
            };

            Actions.ModeraBackendSecurity_Users.get(requestParams, function(response) {
                var user = response.result;

                var permissionsStore = Ext.create('Modera.backend.security.toolscontribution.store.Permissions', {
                    autoload: false
                });
                permissionsStore.load(function(records, operation, success) {
                    var data = [];
                    Ext.each(records, function(record) {
                        var permission = Ext.apply({}, record.data);
                        permission['users'] = [];

                        if (record.data['users'].indexOf(user['id']) !== -1) {
                            permission['users'].push(user['id']);
                        }

                        Ext.each(record.data['groups'], function(group) {
                            if (user['groups'].indexOf(group) !== -1) {
                                permission['users'].push(0);
                                return false; // stop iteration
                            }
                        });

                        data.push(permission);
                    });

                    var store = Ext.create('Ext.data.Store', {
                        fields: ['id', 'name', 'category', 'users'],
                        groupers: [
                            {
                                getGroupString: function(record) {
                                    return record.get('category')['name'];
                                }
                            }
                        ],
                        data: data
                    });

                    var groupsStore = Ext.create('Ext.data.Store', {
                        fields: ['id', 'name'],
                        data: [
                            {
                                id: 0,
                                name: '-',
                            },
                            {
                                id: user['id'],
                                name: user['fullname'] || user['username'],
                            }
                        ]
                    });

                    var grid = Ext.create('Modera.backend.security.toolscontribution.view.permission.List', {
                        hasAccess: isAllowed,
                        groupsType: 'users',
                        groupsStore: groupsStore,
                        firstColumnFlex: 2,
                        store: store
                    });

                    var windowHeight = 700;
                    var maxHeight = Ext.getBody().getViewSize().height - 60;

                    if (loadMask) {
                        loadMask.setLoading(false);
                    }

                    callback(Ext.widget({
                        extensionPoint: 'permissionsWindow',
                        xtype: 'mfc-modalwindow',
                        layout: 'fit',
                        title: grid.titleText,
                        bodyPadding: '0 0 10',
                        resizable: false,
                        autoScroll: true,
                        width: 800,
                        height: windowHeight > maxHeight ? maxHeight : windowHeight,
                        items: [
                            grid
                        ]
                    }));
                });
            });
        });
    },

    // protected
    attachListeners: function(ui) {
        var me = this;

        ui.down('modera-backend-security-permission-list').on('groupchange', function(sourceComponent, params) {
            Actions.ModeraBackendSecurity_Users.update({ record: params }, function(response) {});
        });
    },

    // private
    setLoading: function() {
        var me = this;

        var workbenchUi = null;
        var activities = me.workbench.getActivitiesManager().getActiveActivities();
        if (activities[activities.length - 1]) {
            try {
                workbenchUi = activities[activities.length - 1].getUi().up('#workbench');
                workbenchUi.setLoading(true);
            } catch (e) {}
        }

        return workbenchUi;
    }
});