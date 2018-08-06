/**
 * @author Sergei Vizel <sergei.vizel@modera.org>
 */
Ext.define('Modera.backend.languages.view.language.DeleteWindow', {
    extend: 'MFC.window.DeleteRecordConfirmationWindow',
    alias: 'widget.modera-backend-languages-language-deletewindow',

    // l10n
    titleText: 'Delete language',
    confirmationMessageText: 'Are you sure you want to delete "{0}" ?',

    // override
    constructor: function(config) {
        var me = this;

        var defaults = {
            defaults: {
                style: 'text-align: center;',
                bodyStyle: 'font-size: 18px; padding-bottom: 10px;'
            },
            layout: {
                type: 'vbox',
                pack: 'center',
                align: 'stretch'
            }
        };

        this.config = Ext.apply(defaults, config || {});
        this.callParent([this.config]);
    }
});