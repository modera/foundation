/**
 * @author Sergei Vizel <sergei.vizel@modera.org>
 */
Ext.define('Modera.backend.languages.view.language.EditWindow', {
    extend: 'Modera.backend.languages.view.language.NewWindow',
    alias: 'widget.modera-backend-languages-language-editwindow',

    // l10n
    editRecordTitleText: 'Edit language',

    // override
    constructor: function(config) {
        var me = this;

        me.editRecordTitle = me.editRecordTitleText;

        var defaults = {
            type: 'edit'
        };

        this.config = Ext.apply(defaults, config || {});
        this.callParent([this.config]);
    }
});