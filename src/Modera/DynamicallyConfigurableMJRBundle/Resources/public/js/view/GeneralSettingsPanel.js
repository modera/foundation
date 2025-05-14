/**
 * @copyright 2014 Modera Foundation
 */
Ext.define('Modera.backend.dcmjr.view.GeneralSettingsPanel', {
    extend: 'Ext.panel.Panel',

    requires: [
        'MFC.form.field.InPlace',
        'MFC.form.field.OnOff'
    ],

    // l10n
    siteTitleLabelText: 'Site title',
    primaryAddressLabelText: 'Primary address',
    defaultSectionLabelText: 'Default section',
    skinCssLabelText: 'Skin CSS URL',
    mjrExtJsLabelText: 'JS runtime extension URL',
    logoUrlLabelText: 'Logo URL',
    developmentModeLabelText: 'Development mode',
    debugModeLabelText: 'Debug mode',

    /**
     * @param {Object} config
     */
    constructor: function(config) {
        var defaults = {
            frame: true,
            bodyPadding: 35,
            items: {
                xtype: 'form',
                defaults: {
                    labelAlign: 'right',
                    labelWidth: 170,
                    anchor: '100%'
                },
                items: [
                    {
                        name: 'site_name',
                        fieldLabel: this.siteTitleLabelText,
                        xtype: 'mfc-inplacefield',
                        htmlEncode: true
                    },
                    {
                        name: 'url',
                        fieldLabel: this.primaryAddressLabelText,
                        xtype: 'mfc-inplacefield',
                        htmlEncode: true
                    },
                    // {
                    //     name: 'home_section',
                    //     fieldLabel: this.defaultSectionLabelText,
                    //     xtype: 'mfc-inplacefield',
                    //     htmlEncode: true
                    // },
                    {
                        name: 'skin_css',
                        fieldLabel: this.skinCssLabelText,
                        xtype: 'mfc-inplacefield',
                        htmlEncode: true
                    },
                    {
                        name: 'mjr_ext_js',
                        fieldLabel: this.mjrExtJsLabelText,
                        xtype: 'mfc-inplacefield',
                        htmlEncode: true
                    },
                    {
                        name: 'logo_url',
                        fieldLabel: this.logoUrlLabelText,
                        xtype: 'mfc-inplacefield',
                        htmlEncode: true
                    },
                    {
                        xtype: 'mfc-onofffield',
                        fieldLabel: this.developmentModeLabelText,
                        name: 'kernel_env',
                        onValue: 'dev',
                        offValue: 'prod'
                    },
                    {
                        xtype: 'mfc-onofffield',
                        fieldLabel: this.debugModeLabelText,
                        name: 'kernel_debug',
                        onValue: 'true',
                        offValue: 'false'
                    }
                ]
            }
        };

        this.config = Ext.apply(defaults, config || {});

        this.callParent([this.config]);
    }
});