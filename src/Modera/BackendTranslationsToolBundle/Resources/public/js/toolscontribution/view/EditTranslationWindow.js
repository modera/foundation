/**
 * @copyright 2014 Modera Foundation
 */
Ext.define('Modera.backend.translationstool.toolscontribution.view.EditTranslationWindow', {
    extend: 'MFC.window.NewAndEditRecordWindow',
    alias: 'widget.modera-backend-translations-tool-editwindow',

    // l10n
    editRecordTitleText: 'Edit translation',
    placeHolderText: 'Type here',
    domainLabelText: 'Domain',
    tokenNameLabelText: 'Token name',
    transtationLabelText: 'Translation',

    // override
    constructor: function(config) {
        var me = this;

        var defaults = {
            type: 'edit',
            groupName: 'main-form',
            resizable: false,
            autoScroll: true,
            width: 800,
            maxHeight: Ext.getBody().getViewSize().height - 60,
            tid: 'translationTokenEditWindow',
            items: {
                xtype: 'form',
                defaultType: 'displayfield',
                layout: {
                    type: 'vbox',
                    align: 'stretch'
                },
                defaults: {
                    labelAlign: 'right'
                },
                items: [
                    {
                        xtype: 'hiddenfield',
                        name: 'id'
                    },
                    {
                        name: 'domainName',
                        fieldLabel: me.domainLabelText,
                        renderer: function(value, field) {
                            if (!this.rndTpl) {
                                this.rndTpl = new Ext.XTemplate(
                                    '<a href="#">{[values.domainName]}</a>'
                                );
                            }
                            return this.rndTpl.apply({
                                domainName: value
                            });
                        },
                        listeners: {
                            afterrender: function(view) {
                                view.getEl().on('click', function(e) {
                                    e.stopEvent();
                                    me.fireEvent('filterandclose', me, view.value);
                                })
                            }
                        }
                    },
                    {
                        name: 'tokenName',
                        fieldLabel: me.tokenNameLabelText,
                        listeners: {
                            resize: function(field, width, height) {
                                if (height > 200) {
                                    field.setFieldStyle({
                                        height: 200,
                                        display: 'block',
                                        overflow: 'auto'
                                    });
                                    field.setHeight(220);

                                    me.down('textarea').setHeight(300);
                                    me.center();
                                }
                            }
                        }
                    },
                    {
                        itemId: 'translation',
                        xtype: 'textarea',
                        name: 'translation',
                        fieldLabel: me.transtationLabelText,
                        emptyText: me.placeHolderText,
                        enterIsSpecial: true,
                        enableKeyEvents: true,
                        tid: 'translationField'
                    }
                ]
            }
        };

        this.config = Ext.apply(defaults, config || {});
        this.callParent([this.config]);

        me.assignListeners();
    },

    loadData: function(data) {
        var me = this;
        me.setTitle(me.editRecordTitleText + ' "' + data.languageName + '"');
        me.down('form').getForm().setValues(data);
    },

    // private
    assignListeners: function() {
        var me = this;

//        me.down('#translation').on('focus', function(field, e) {
//            field.focus(true);
//        });

        me.down('#translation').on('keydown', function(field, e) {
            if (e.getKey() == e.SHIFT) {
                me.shiftKeyPressed = true;
            }
        });
        me.down('#translation').on('keyup', function(field, e) {
            if (e.getKey() == e.SHIFT) {
                me.shiftKeyPressed = false;
            }
        });
        me.down('#translation').on('specialkey', function(field, e) {
            if (!me.shiftKeyPressed && e.getKey() == e.ENTER) {
                e.stopEvent();
                if (false !== me.fireEvent('beforesaveandclose', me)) {
                    me.fireEvent('saveandclose', me);
                }
            }
        });
    }
});