/**
 * @copyright 2014 Modera Foundation
 */
Ext.define('Modera.backend.tools.settings.view.HostPanel', {
    extend: 'Ext.panel.Panel',

    requires: [
        'MFC.container.Header',
        'MFC.panel.Message',
        'Ext.util.Format'
    ],

    // l10n
    titleText: 'Settings',
    noSectionsMessageText: 'No sections available',
    noSectionSelectedText: 'No section selected',

    /**
     * @param {Object} config
     */
    constructor: function(config) {
        var defaults = {
            basePanel: true,
            padding: 10,
            layout: 'fit',
            tid: 'settingManagerPanel',
            cls: 'modera-backend-tools-settings',
            tbar: {
                xtype: 'mfc-header',
                text: this.titleText,
                margin: '0 0 9 0',
                cls: 'tools-settings-toolbar',
                iconCls: 'modera-backend-tools-settings-icon',
                items: [
                    {
                        xtype: 'image',
                        glyph: FontAwesome.resolve('chevron-right', 'fas'),
                        margin: '0 13 0 13',
                        bodyPadding: '2 0',
                        cls: 'section-separator'
                    },
                    {
                        itemId: 'sectionNameBox',
                        xtype: 'box',
                        cls: 'text',
                        html: this.noSectionSelectedText
                    },
                    '->'
                ]
            },
            items: {
                xtype: 'container',
                layout: {
                    type: 'hbox',
                    align: 'stretch'
                },
                split: true,
                items: [
                    {
                        tid: 'settingMenuGrid',
                        itemId: 'sectionsGrid',
                        width: 200,
                        xtype: 'grid',
                        hideHeaders: true,
                        border: false,
                        bodyBorder: false,
                        columnLines: false,
                        rowLines: false,
                        cls: 'tools-settings-sections-grid',
                        margin: '0 11 0 0',
                        columns: [
                            {
                                dataIndex: 'glyph',
                                width: 30,
                                renderer: function(v, md, record) {
                                    md.tdCls = record.get('parent') ? 'left section-child' : 'left';
                                    if (!v || record.get('parent')) {
                                        return '';
                                    }

                                    var glyph = v.search('@') > 0 ? v : FontAwesome.resolve(v),
                                        glyphParts = glyph.split('@');

                                    return Ext.String.format(
                                        '<span style="font-family: {1};">{0}</span>', '&#' + glyphParts[0] + ';', glyphParts[1]
                                    )
                                }
                            },
                            {
                                dataIndex: 'name',
                                flex: 1,
                                renderer: function(v, md, record) {
                                    md.tdCls = record.get('parent') ? 'right section-child' : 'right';

                                    return Ext.util.Format.htmlEncode(v);
                                }
                            }
                        ],
                        store: Ext.create('Ext.data.Store', {
                            fields: [
                                'id', 'name', 'glyph',
                                { name: 'parent', defaultValue: null },
                                { name: 'group', type: 'boolean', defaultValue: false },
                                { name: 'hasChildren', type: 'boolean', defaultValue: false },
                                { name: 'expanded', type: 'boolean', defaultValue: false }
                            ],
                            proxy: {
                                type: 'memory'
                            }
                        })
                    },
                    {
                        itemId: 'hostPanel',
                        layout: 'card',
                        flex: 1,
                        items: [
                            {
                                border: true,
                                xtype: 'mfc-pmsg',
                                msg: this.noSectionsMessageText
                            }
                        ]
                    }
                ]
            }
        };

        this.config = Ext.apply(defaults, config || {});
        this.callParent([this.config]);

        this.addEvents(
            /**
             * @event showsection
             */
            'showsection'
        );

        this.assignListeners();
    },

    /**
     * @param {Object} section
     */
    addSection: function(section) {
        var ui = section.ui;
        delete section.ui;

        var parent = section.parent ? this.findSection(section.parent) : null;
        if (parent && !parent.get('parent')) {
            parent.set('hasChildren', true);
        } else {
            section.parent = null;
        }

        var store = this.down('#sectionsGrid').getStore();
        store.clearFilter(true);
        store.add(section);
        this.refreshVisibleSections();

        if (ui) {
            this.down('#hostPanel').add(ui);
        }
    },

    /**
     * Finds a section among all sections, including ones hidden inside collapsed parents.
     *
     * @param {String} id
     * @return {Ext.data.Model}
     */
    findSection: function(id) {
        var store = this.down('#sectionsGrid').getStore();
        var all = store.snapshot || store.data;

        return all.findBy(function(record) {
            return record.get('id') === id;
        });
    },

    /**
     * @param {Ext.data.Model} record
     * @return {Ext.data.Model}
     */
    findFirstChild: function(record) {
        var store = this.down('#sectionsGrid').getStore();
        var all = store.snapshot || store.data;

        return all.findBy(function(child) {
            return child.get('parent') === record.get('id');
        });
    },

    /**
     * Expands a section and collapses the others.
     *
     * @param {Ext.data.Model} record
     */
    expandSection: function(record) {
        if (record.get('hasChildren') && !record.get('expanded')) {
            this.collapseSectionsExcept([record]);
            record.set('expanded', true);
            this.refreshVisibleSections();
        }
    },

    // private
    collapseSectionsExcept: function(keep) {
        var store = this.down('#sectionsGrid').getStore();

        (store.snapshot || store.data).each(function(record) {
            if (record.get('expanded') && keep.indexOf(record) === -1) {
                record.set('expanded', false);
            }
        });
    },

    // private
    refreshVisibleSections: function() {
        var me = this;
        var store = me.down('#sectionsGrid').getStore();

        store.clearFilter(true);
        store.filterBy(function(record) {
            var parent = me.findSection(record.get('parent'));

            return !parent || parent.get('expanded');
        });

        me.selectActiveSection();
    },

    // private
    selectActiveSection: function() {
        var grid = this.down('#sectionsGrid');
        var store = grid.getStore();
        var active = this.activeSectionId ? store.findRecord('id', this.activeSectionId, 0, false, true, true) : null;

        if (active && this.rendered) {
            grid.getSelectionModel().select(active, false, true);
        }
    },

    /**
     * @param {String} id
     */
    showSection: function(id) {
        var sectionsGrid = this.down('#sectionsGrid');
        var record = this.findSection(id);

        if (record && !record.get('group')) {
            this.activeSectionId = id;

            var parent = this.findSection(record.get('parent'));
            this.collapseSectionsExcept([parent, record]);
            if (parent) {
                parent.set('expanded', true);
            }
            record.set('expanded', record.get('hasChildren'));
            this.refreshVisibleSections();

            var hostPanel = this.down('#hostPanel'),
                activityContainer = hostPanel.down(Ext.String.format('component[activity={0}]', id));

            if (activityContainer) {
                var oldContainer = hostPanel.getLayout().getActiveItem(),
                    newContainer = hostPanel.getLayout().setActiveItem(activityContainer);

                if (this.rendered) {
                    sectionsGrid.getSelectionModel().select(record);
                } else {
                    this.on('render', function() {
                        sectionsGrid.getSelectionModel().select(record);
                    });
                }

                this.down('#sectionNameBox').update(Ext.util.Format.htmlEncode(record.get('name')));

                hostPanel.fireEvent('activitychange', hostPanel, newContainer, oldContainer);

                return newContainer;
            }
        }

        return false;
    },

    // private
    assignListeners: function() {
        var me = this;

        var sectionsGrid = this.down('#sectionsGrid');

        sectionsGrid.on('beforeselect', function(selModel, record) {
            return !record.get('group');
        });

        // the grid changes the selection on mouse down and on click; groups cancel both, so the open
        // section stays selected
        sectionsGrid.on('beforeitemmousedown', function(view, record) {
            return !record.get('group');
        });

        sectionsGrid.on('beforeitemclick', function(view, record) {
            if (!record.get('group')) {
                return;
            }

            if (!record.get('expanded')) {
                var firstChild = me.findFirstChild(record);
                var active = me.findSection(me.activeSectionId);

                me.expandSection(record);
                if (firstChild && !(active && active.get('parent') === record.get('id'))) {
                    me.fireEvent('showsection', view, firstChild.data);
                }
            }

            return false;
        });

        sectionsGrid.on('itemclick', function(grid, record) {
            if (!(record.get('expanded') && me.activeSectionId === record.get('id'))) {
                me.fireEvent('showsection', grid, record.data);
            }
        });
    }
});