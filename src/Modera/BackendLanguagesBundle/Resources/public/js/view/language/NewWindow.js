/**
 * @author Sergei Vizel <sergei.vizel@modera.org>
 */
Ext.define('Modera.backend.languages.view.language.NewWindow', {
    extend: 'MFC.window.NewAndEditRecordWindow',
    alias: 'widget.modera-backend-languages-language-newwindow',

    // l10n
    newRecordTitleText: 'Add new language',
    placeHolderText: 'Select language',
    activeLabelText: 'This language is active',

    // override
    constructor: function(config) {
        var me = this;
        
        me.newRecordTitle = me.newRecordTitleText;

        var ignore = (config['dto'] && config['dto']['ignore']) || [];

        var defaults = {
            type: 'new',
            groupName: 'list',
            resizable: false,
            autoScroll: true,
            width: 500,
            maxHeight: Ext.getBody().getViewSize().height - 60,
            layout: 'fit',
            items: {
                xtype: 'form',
                defaultType: 'textfield',
                layout: {
                    type: 'vbox',
                    align: 'stretch'
                },
                defaults: {
                    labelAlign: 'right'
                },
                items: [
                    {
                        name: 'id',
                        xtype: 'hiddenfield'
                    },
                    {
                        name: 'locale',
                        xtype: 'combo',
                        emptyText: me.placeHolderText,
                        store: Ext.create('Modera.backend.languages.store.Locales', {
                            autoLoad: true,
                            extraParams: {
                                ignore: ignore
                            }
                        }),
                        listConfig: {
                            getInnerTpl: function(displayField) {
                                return '{[Ext.util.Format.htmlEncode(values.' + displayField + ')]}';
                            }
                        },
                        queryMode: 'local',
                        displayField: 'name',
                        valueField: 'id',
                        allowBlank: false,
                        editable: false
                    },
                    {
                        name: 'isEnabled',
                        xtype: 'checkboxfield',
                        boxLabel: me.activeLabelText,
                        labelSeparator: '',
                        inputValue: true,
                        uncheckedValue: false
                    }
                ]
            }
        };

        this.config = Ext.apply(defaults, config || {});
        this.callParent([this.config]);

        this.assignListeners();
    },

    // private
    assignListeners: function() {
        var me = this;
    },

    loadData: function(data) {
        var me = this;
        me.down('form').getForm().setValues(data);
    }
});