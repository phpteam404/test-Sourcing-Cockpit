/**
 * @license Copyright (c) 2003-2016, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or http://ckeditor.com/license
 *  config.toolbar_Full  = [
 { name: 'document',    items : [ 'Source','-','Save','NewPage','DocProps','Preview','Print','-','Templates' ] },
 { name: 'clipboard',   items : [ 'Cut','Copy','Paste','PasteText','PasteFromWord','-','Undo','Redo' ] },
 { name: 'editing',     items : [ 'Find','Replace','-','SelectAll','-','SpellChecker', 'Scayt' ] },
 { name: 'forms',       items : [ 'Form', 'Checkbox', 'Radio', 'TextField', 'Textarea', 'Select', 'Button', 'ImageButton', 'HiddenField' ] },
 '/',
 { name: 'basicstyles', items : [ 'Bold','Italic','Underline','Strike','Subscript','Superscript','-','RemoveFormat' ] },
 { name: 'paragraph',   items : [ 'NumberedList','BulletedList','-','Outdent','Indent','-','Blockquote','CreateDiv','-','JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock','-','BidiLtr','BidiRtl' ] },
 { name: 'links',       items : [ 'Link','Unlink','Anchor' ] },
 { name: 'insert',      items : [ 'Table'] },
 '/',
 { name: 'styles',      items : [ 'Styles','Format','Font','FontSize' ] },
 { name: 'colors',      items : [ 'TextColor','BGColor' ] },
 { name: 'tools',       items : [ 'Maximize', 'ShowBlocks','-','About' ] }
 ];
 */

CKEDITOR.editorConfig = function( config ) {
	// Define changes to default configuration here. For example:
	// config.language = 'fr';
	// config.uiColor = '#AADC6E';
    config.allowedContent = true;
    config.filebrowserUploadUrl = API_URL+'Editor/upload';
    config.imageUploadUrl = API_URL+'Editor/upload';
    config.toolbar= [
        //{ name: 'clipboard',   items : [ 'Cut','Copy','Paste','PasteText','PasteFromWord','-','Undo','Redo' ] },
        { name: 'basicstyles', items : [ 'Bold','Italic','Underline'] },
        { name: 'links',       items : [ 'Link','Unlink' ] },
        { name: 'insert',      items : [ 'Table','Image'] },
        { name: 'colors',      items : [ 'TextColor','BGColor' ] },
        { name: 'styles',      items : [ 'Format','FontSize' ] },
        { name: 'tools',       items : [ 'Maximize'] },
        '/',
        { name: 'paragraph',   items : [ 'NumberedList','BulletedList','-','Outdent','Indent','-','Blockquote','CreateDiv','-','JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock','-','BidiLtr','BidiRtl','image' ] }

    ];


};
