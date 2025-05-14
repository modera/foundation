/**
 * @copyright 2018 Modera Foundation
 */
Ext.define('Modera.backend.languages.runtime.SettingsActivity', {
    extend: 'Modera.backend.languages.runtime.ListActivity',

    // override
    getId: function() {
        return 'settings';
    },

    handleAction: function(actionName, sourceComponent, params) {
        var me = this;

        if (-1 !== ['newlanguage', 'editlanguage', 'removelanguage'].indexOf(actionName)) {
            var fqcn = 'Modera.backend.languages.runtime.language.NewWindowActivity';
            if ('editlanguage' == actionName) {
                fqcn = 'Modera.backend.languages.runtime.language.EditWindowActivity';
            } else if ('removelanguage' == actionName) {
                fqcn = 'Modera.backend.languages.runtime.language.DeleteWindowActivity';
            }
            Ext.create(fqcn).activate(params, function(activity, params, ui) {
                ui.show();
            });

        } else {
            me.callParent(arguments);
        }
    }
});