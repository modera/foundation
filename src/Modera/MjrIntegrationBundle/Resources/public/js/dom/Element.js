
Ext.define('Ext.dom.Element_fix', {
    override: 'Ext.dom.Element',

    getWidth: function(contentWidth, preciseWidth) {
        if (Ext.isGecko || Ext.isMac) {
            return Math.ceil(this.callParent([contentWidth, true]));
        } else {
            return this.callParent(arguments);
        }
    }
});