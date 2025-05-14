/**
 * @copyright 2014 Modera Foundation
 */
Ext.define('Modera.backend.security.toolscontribution.controller.Manager', {
    extend: 'Ext.app.Controller',

    // override
    init: function() {
        this.callParent(arguments);

        this.control({
            'modera-backend-security-manager mfc-header': {
                close: this.onClose
            }
        });
    },

    // private
    onClose: function() {
        this.application.getContainer().get('workbench').activateSection('tools');
    }
});