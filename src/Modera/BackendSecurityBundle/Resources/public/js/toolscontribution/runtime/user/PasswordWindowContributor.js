/**
 * @author Sergei Vizel <sergei.vizel@modera.org>
 */
Ext.define('Modera.backend.security.toolscontribution.runtime.user.PasswordWindowContributor', {
    extends: 'MF.runtime.SharedActivitiesProviderInterface',

    requires: [
        'MF.Util',
        'MF.intent.IntentManager',
        'Modera.backend.security.toolscontribution.runtime.user.PasswordWindowActivity'
    ],

    // l10n
    changePasswordBtnText: 'Change password',

    // override
    constructor: function(application) {
        var me = this;

        me.application = application;
        me.passwordWindowActivity = Ext.create('Modera.backend.security.toolscontribution.runtime.user.PasswordWindowActivity');

        me.contributeButton('mf-theme-header', 'profileContextMenuActions', this.onContributedButtonClicked);
    },

    // override
    getSharedActivities: function(section) {
        var me = this;

        return [me.passwordWindowActivity];
    },

    // private
    contributeButton: function(uiCmp, extensionPoint, callback) {
        var me = this;

        var query = uiCmp + ' component[extensionPoint=' + extensionPoint + ']';

        var lookup = {};
        lookup[query] =  {
            render: function(menu) {
                menu.add({
                    tid: 'changepasswordbtn',
                    text: me.changePasswordBtnText,
                    contributedBy: me,
                    handler: callback,
                    scope: me
                })
            }
        };

        MF.Util.control(lookup);
    },

    // private
    onContributedButtonClicked: function(btn) {
        var me = this;

        var workbench = me.application.getContainer().get('workbench');
        workbench.getService('config_provider').getConfig(function(config) {
            var intentMgr = workbench.getService('intent_manager');
            intentMgr.dispatch({
                name: 'edit-password',
                params: { id: config['userProfile']['id'], meta: config['userProfile']['meta'] }
            }, function(intent) {
                if (false === intent) {
                    workbench.launchActivity('edit-password', {
                        id: config['userProfile']['id']
                    });
                }
            }, [
                MF.intent.IntentManager.OPTION_USE_FIRST_HANDLER,
                MF.intent.IntentManager.OPTION_SKIP_NO_HANDLERS_REPORTING
            ]);
        });
    }
});
