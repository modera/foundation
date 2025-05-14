/**
 * @copyright 2013 Modera Foundation
 */
Ext.define('Modera.backend.tools.controller.Controller', {
    extend: 'Ext.app.Controller',

    // override
    init: function() {
        this.control({
            'modera-backend-tools-hostpanel': {
                changesection: this.onChangeSection
            }
        })
    },

    // private
    onChangeSection: function(hostPanel, section) {
        this.application.getContainer().get('workbench').activateSection(section.get('section'));
    }
});