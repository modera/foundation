/**
 * @author Sergei Vizel <sergei.vizel@modera.org>
 */
Ext.define('Modera.mjrsecurityintegration.runtime.UserSettingsContributor', {
    extends: 'MF.runtime.SharedActivitiesProviderInterface',

    requires: [
        'MF.Util',
        'Modera.mjrsecurityintegration.runtime.SwitchUserWindowActivity'
    ],

    // l10n
    switchUserBtnText: 'Switch user to',

    // override
    constructor: function(application, role) {
        var me = this;
        var sm = application.getContainer().get('workbench').getService('security_manager');

        me.application = application;
        me.activity = Ext.create('Modera.mjrsecurityintegration.runtime.SwitchUserWindowActivity');

        sm.isAllowed('ROLE_PREVIOUS_ADMIN', function(isAllowed) {
            if (!isAllowed) {
                sm.isAllowed(role, function(isAllowed) {
                    if (isAllowed) {
                        me.contributeButton(
                            'mf-theme-header component[extensionPoint=profileContextMenuActions]',
                            me.onContributedButtonClicked
                        );
                    }
                });
            }
        });
    },

    // override
    getSharedActivities: function(section) {
        var me = this;

        return [me.activity];
    },

    // private
    contributeButton: function(query, callback) {
        var me = this;

        var lookup = {};
        lookup[query] =  {
            render: function(menu) {
                menu.add({
                    text: me.switchUserBtnText,
                    contributedBy: me,
                    handler: callback,
                    scope: me
                });
            }
        };

        MF.Util.control(lookup);
    },

    // private
    onContributedButtonClicked: function(btn) {
        var me = this;

        var workbench = me.application.getContainer().get('workbench');
        workbench.launchActivity('switch-user', {});
    }
});
