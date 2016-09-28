/*
 * Copyright (C) eZ Systems AS. All rights reserved.
 * For full copyright and license information view LICENSE file distributed with this source code.
 */
YUI.add('ez-universaldiscoverymethodbaseview', function (Y) {
    "use strict";
    /**
     * Provides the Universal discovery method base view
     *
     * @module ez-universaldiscoverymethodbaseview
     */
    Y.namespace('eZ');

    /**
     * Universal Discovery method base view class. This class is meant to be
     * extended to provide a discovery method in the universal discovery view.
     * When extending this class, the minimum is to set a `title` and an
     * `identifier` to the method and of course to implement the discovery
     * logic.
     *
     * @namespace eZ
     * @class UniversalDiscoveryMethodBaseView
     * @constructor
     * @extends eZ.TemplateBasedView
     */
    Y.eZ.UniversalDiscoveryMethodBaseView = Y.Base.create('universalDiscoveryMethodBaseView', Y.eZ.TemplateBasedView, [], {
        /**
         * Returns the HTML identifier of the method
         *
         * @method getHTMLIdentifier
         * @return {String}
         */
        getHTMLIdentifier: function () {
            return 'ez-ud-' + this.get('identifier');
        },

        /**
         * Method called when a content is removed from the universal discovery
         * view selection. The default implementation does nothing, it is meant
         * to be overriden.
         *
         * @method onUnselectContent
         * @param {String} contentId
         */
        onUnselectContent: function (contentId) {
        },
    }, {
        ATTRS: {
            /**
             * Stores the title of the method. It is displayed in the tab label
             * generated by the universal discovery view
             *
             * @attribute title
             * @type {String}
             * @readOnly
             */
            title: {
                value: "",
                readOnly: true,
            },

            /**
             * Stores the identifier of the method. It is used to generate the
             * identifier of the tab panel and to set the visible method in the
             * universal discovery view
             *
             * @attribute identifer
             * @type {String}
             * @readOnly
             */
            identifier: {
                value: "",
                readOnly: true,
            },

            /**
             * Priority of the method
             *
             * @attribute priority
             * @type {Number}
             * @default 0
             */
            priority: {
                value: 0,
            },

            /**
             * Flag indicating whether the user is able to select several
             * contents.
             *
             * @attribute multiple
             * @type {Boolean}
             * @default false
             */
            multiple: {
                value: false,
            },

            /**
             * The Location Id where the content discovery can start.
             *
             * @attribute multiple
             * @type {String}
             * @default false if there's no starting location
             */
            startingLocationId: {
                value: false,
            },

            /**
             * Flag indicating whether the Content should be provided in the
             * selection.
             *
             * @attribute loadContent
             * @type {Boolean}
             * @default false
             */
            loadContent: {
                value: false,
            },

            /**
             * The visible flag. it is updated by the universal discovery view
             * depending on the tab state.
             *
             * @attribute visible
             * @type {Boolean}
             * @default false
             */
            visible: {
                value: false,
            },

            /**
             * Checks wether the content is already selected.
             *
             * @attribute isAlreadySelected
             * @type {Function}
             */
            isAlreadySelected: {
                validator: Y.Lang.isFunction,
                value: function (contentStruct) {
                    return false;
                }
            },

            /**
             * Checks wether the content is selectable.
             *
             * @attribute isSelectable
             * @type {Function}
             */
            isSelectable: {
                validator: Y.Lang.isFunction,
                value: function (contentStruct) {
                    return true;
                }
            },
        },
    });
});