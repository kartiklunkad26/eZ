/*
 * Copyright (C) eZ Systems AS. All rights reserved.
 * For full copyright and license information view LICENSE file distributed with this source code.
 */
YUI.add('ez-textblock-editview', function (Y) {
    "use strict";
    /**
     * Provides the field edit view for the Text Block (eztext) fields
     *
     * @module ez-textblock-editview
     */
    Y.namespace('eZ');

    var FIELDTYPE_IDENTIFIER = 'eztext';

    /**
     * Text Block edit view
     *
     * @namespace eZ
     * @class TextBlockEditView
     * @constructor
     * @extends eZ.FieldEditView
     */
    Y.eZ.TextBlockEditView = Y.Base.create('textBlockEditView', Y.eZ.FieldEditView, [], {
        events: {
            '.ez-textblock-input-ui textarea': {
                'blur': 'validate',
                'valuechange': 'validate'
            }
        },

        /**
         * Validates the current input of text block
         *
         * @method validate
         */
        validate: function () {
            if ( this._getInputValidity().valueMissing ) {
                this.set('errorStatus', 'This field is required');
            } else {
                this.set('errorStatus', false);
            }
        },

        /**
         * Defines the variables to imported in the field edit template for text
         * block.
         *
         * @protected
         * @method _variables
         * @return {Object} containing isRequired entry
         */
        _variables: function () {
            return {
                "isRequired": this.get('fieldDefinition').isRequired
            };
        },

        /**
         * Returns the input validity state object for the input generated by
         * the text block template
         *
         * See https://developer.mozilla.org/en-US/docs/Web/API/ValidityState
         *
         * @protected
         * @method _getInputValidity
         * @return {ValidityState}
         */
        _getInputValidity: function () {
            return this._getTextareaNode().get('validity');
        },

        /**
         * Returns the currently filled value of the text block field
         *
         * @method _getFieldValue
         * @protected
         * @return String
         */
        _getFieldValue: function () {
            return this._getTextareaNode().get('value');
        },

        /**
         * Returns the Y.Node of the textarea used for the user input
         *
         * @method _getTextareaNode
         * @private
         * @return {Y.Node}
         */
        _getTextareaNode: function () {
            return this.get('container').one('.ez-textblock-input-ui textarea');
        },
    });

    Y.eZ.FieldEditView.registerFieldEditView(
        FIELDTYPE_IDENTIFIER, Y.eZ.TextBlockEditView
    );
});