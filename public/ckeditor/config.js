/**
 * @license Copyright (c) 2003-2015; CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or http://ckeditor.com/license
 */

CKEDITOR.plugins.addExternal('ckeditor_wiris', 'plugins/ckeditor_wiris/plugin.js');



CKEDITOR.editorConfig = function( config ) {
    config.extraPlugins = 'clipboard,pastefromword,pastefromgdocs,advanced,simpleuploads,quicktable,panelbutton,button,floatpanel,panel,ckeditor_wiris,autogrow';
    config.allowedContent = true;
    config.disableNativeSpellChecker = true;

    config.filebrowserUploadUrl = '/cms/ckeditor_upload/files';
    // config.filebrowserUploadUrl = 'base64';
    config.filebrowserImageUploadUrl = '/cms/ckeditor_upload/images';
    // config.filebrowserImageUploadUrl = 'base64';
    config.simpleuploads_acceptedExtensions = "jpg|jpeg|gif|png";
    config.fileTools_requestHeaders = {'X-CSRF-TOKEN': document.querySelector('meta[name="_token"]').content};
    config.toolbarCanCollapse = true;

    simpleuploads_acceptedExtensions : 'jpeg|jpg|png|PNG';

    // Quick table configuration.
    qtRows: 20; // Count of rows
    qtColumns: 20; // Count of columns
    qtBorder: '1'; // Border of inserted table
    qtWidth: '90%'; // Width of inserted table
    // qtStyle: {
    // 	'border-collapse' : 'collapse'
    // };
    qtClass: 'test'; // Class of table
    qtCellPadding: '0'; // Cell padding table
    qtCellSpacing: '0'; // Cell spacing table
    qtPreviewBorder: '4px double black'; // preview table border
    qtPreviewSize: '4px'; // Preview table cell size
    qtPreviewBackground: '#c8def4'; // preview table background (hover)
    config.extraCss = "body{font-size:1.3em;}";

    config.font_names =
    'Arial/Arial, Helvetica, sans-serif;' +
    'calibri;' +
    'Times New Roman/Times New Roman, Times, serif;' +
    'Verdana';

    config.width = 'auto';
    config.toolbar = [
        { name: 'clipboard', items: [ 'Undo', 'Redo' ] },
        // { name: 'clipboard', items: [ 'PasteFromWord', '-', 'Undo', 'Redo' ] },
        { name: 'basicstyles', items: [ 'Bold', 'Italic', 'Underline', 'Strike', 'RemoveFormat', 'Subscript', 'Superscript' ] },
        { name: 'paragraph', items: [ 'NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote' ] },
        { name: 'insert', items: [ 'addImage', 'Table' ] },
        //{ name: 'editing', items: [ 'EqnEditor' ] },
        { name: 'tools' , items: ['Maximize']},
        // { name: 'editing', items: [ 'Scayt', 'EqnEditor' ] },
        '/',
        { name: 'styles', items: [ 'Format', 'Font', 'FontSize' ] },
        { name: 'colors', items: [ 'TextColor', 'BGColor', 'CopyFormatting' ] },
        { name: 'align', items: [ 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock' ] },
        {name: 'wirisplugins', items: ['ckeditor_wiris_formulaEditor', 'ckeditor_wiris_formulaEditorChemistry']}
    ];

    config.mathTypeParameters = {
        serviceProviderProperties : {
            URI : 'integration',
                server : 'php' //change this to 'java' if 'php' does not work for you (for example you're using Vale)
        }
    };

    config.stylesSet = [
        /* Inline Styles */
        { name: 'Marker', element: 'span', attributes: { 'class': 'marker' } },
        { name: 'Cited Work', element: 'cite' },
        { name: 'Inline Quotation', element: 'q' },
        { name: 'Special Container', element: 'div',
            styles: {
                padding: '5px 10px',
                background: '#eee',
                border: '1px solid #ccc'
            }
        },
        { name: 'Compact table', element: 'table',
            attributes: {
                cellpadding: '5',
                cellspacing: '0',
                border: '1',
                bordercolor: '#ccc'
            },
            styles: { 'border-collapse': 'collapse' }
        },
        { name: 'Borderless Table', element: 'table', styles: { 'border-style': 'hidden', 'background-color': '#E6E6FA' } },
        { name: 'Square Bulleted List', element: 'ul', styles: { 'list-style-type': 'square' } }
    ];

    config.autoGrow_minHeight = 250;
    config.autoGrow_maxHeight = getMaxHeightForEditor();
    config.autoGrow_onStartup = true;
    config.autoGrow_bottomSpace = 50;
};


CKEDITOR.studentEditorConfig = {
    removePlugins : 'pastefromword,pastefromgdocs,advanced,simpleuploads,dropoff,copyformatting,image,pastetext,uploadwidget,uploadimage',
    extraPlugins : 'blockimagepaste,quicktable,ckeditor_wiris,wordcount,notification',
    toolbar: [
        { name: 'basicstyles', items: [ 'Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript' ] },
        { name: 'paragraph', items: [ 'NumberedList', 'BulletedList' ] },
        { name: 'insert', items: [ 'Table' ] },
        { name: 'styles', items: ['Font', 'FontSize' ] },
        { name: 'wirisplugins', items: ['ckeditor_wiris_formulaEditor', 'ckeditor_wiris_formulaEditorChemistry']}
    ]
}

function getMaxHeightForEditor() {
    var absoluteMaxHeight = 600;
    var mininmalMaxHeight = 250;

    var fixedElementsHeight = 70*2;
    var virtualKeyboardHeight = 270;

    if ((window.innerHeight - fixedElementsHeight - virtualKeyboardHeight) < mininmalMaxHeight ) {
        return mininmalMaxHeight;
    }

    if((window.innerHeight - fixedElementsHeight - virtualKeyboardHeight) > absoluteMaxHeight) {
        return absoluteMaxHeight;
    }

    return window.innerHeight - fixedElementsHeight - virtualKeyboardHeight;
}