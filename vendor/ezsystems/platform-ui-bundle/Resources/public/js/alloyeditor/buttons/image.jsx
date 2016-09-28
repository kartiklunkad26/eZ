/*
 * Copyright (C) eZ Systems AS. All rights reserved.
 * For full copyright and license information view LICENSE file distributed with this source code.
 */
YUI.add('ez-alloyeditor-button-image', function (Y) {
    "use strict";

    var AlloyEditor = Y.eZ.AlloyEditor,
        React = Y.eZ.React,
        ButtonImage;

    /**
     * The ButtonImage component represents a button to add an image in the
     * editor.
     *
     * @uses AlloyEditor.ButtonCommand
     * @uses AlloyEditor.ButtonStateClasses
     * @uses eZ.AlloyEditorToolbarConfig.ButtonImageDiscoverContent
     *
     * @class eZ.AlloyEditor.ButtonImage
     */
    ButtonImage = React.createClass({
        mixins: [
            AlloyEditor.ButtonCommand,
            AlloyEditor.ButtonStateClasses,
            Y.eZ.AlloyEditorButton.WidgetButton,
            Y.eZ.AlloyEditorButton.ButtonEmbedDiscoverContent,
            Y.eZ.AlloyEditorButton.ButtonEmbedImage,
        ],

        statics: {
            key: 'ezimage'
        },

        getDefaultProps: function () {
            return {
                command: 'ezembed',
                modifiesSelection: true,
                udwTitle: "Select an image to embed",
                udwContentDiscoveredMethod: '_addImage',
                udwIsSelectableMethod: '_isImage',
                udwLoadContent: true,
            };
        },

        /**
         * Executes the command generated by the ezembed plugin and set the
         * correct value based on the choosen image.
         *
         * @method _addImage
         * @param {EventFacade} e the result of the choice in the UDW
         * @protected
         */
        _addImage: function (e) {
            var widget;

            this.execCommand();
            this._setContentInfo(e.selection.contentInfo);

            widget = this._getWidget()
                .setConfig('size', 'medium')
                .setImageType()
                .setWidgetContent('');
            this._fireUpdatedEmbed(e.selection);
            widget.setFocused(true);
        },

        render: function () {
            var css = "ae-button ez-ae-labeled-button" + this.getStateClasses();

            return (
                <button className={css} onClick={this._chooseContent} tabIndex={this.props.tabIndex}>
                    <span className="ez-ae-icon ez-ae-icon-image ez-font-icon"></span>
                    <p className="ez-ae-label">Image</p>
                </button>
            );
        },
    });

    AlloyEditor.Buttons[ButtonImage.key] = AlloyEditor.ButtonImage = ButtonImage;
});