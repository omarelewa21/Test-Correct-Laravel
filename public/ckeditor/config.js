/**
 * @license Copyright (c) 2003-2015; CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or http://ckeditor.com/license
 */

CKEDITOR.plugins.addExternal('ckeditor_wiris', 'plugins/@wiris/mathtype-ckeditor4/', 'plugin.js');



CKEDITOR.editorConfig = function( config ) {
    config.extraPlugins = 'clipboard,pastefromword,advanced,simpleuploads,quicktable,panelbutton,button,floatpanel,panel,ckeditor_wiris';
    config.allowedContent = true;
    config.disableNativeSpellChecker = true;

    config.filebrowserUploadUrl = '/custom/uploader.php?command=QuickUpload&type=Files';
    // config.filebrowserUploadUrl = 'base64';
    config.filebrowserImageUploadUrl = '/custom/uploader.php?command=QuickUpload&type=Images';
    // config.filebrowserImageUploadUrl = 'base64';

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
};


CKEDITOR.studentEditorConfig = {
    removePlugins : 'pastefromword,advanced,simpleuploads,dropoff,copyformatting,image,pastetext,uploadwidget,uploadimage',
    extraPlugins : 'blockimagepaste,quicktable,ckeditor_wiris',
    toolbar: [
        { name: 'basicstyles', items: [ 'Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript' ] },
        { name: 'paragraph', items: [ 'NumberedList', 'BulletedList' ] },
        { name: 'insert', items: [ 'Table' ] },
        { name: 'styles', items: ['Font', 'FontSize' ] },
        { name: 'wirisplugins', items: ['ckeditor_wiris_formulaEditor', 'ckeditor_wiris_formulaEditorChemistry']}
    ]
}