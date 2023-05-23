/**
 * @license Copyright (c) 2014-2022, CKSource Holding sp. z o.o. All rights reserved.
 * For licensing, see LICENSE.md or https://ckeditor.com/legal/ckeditor-oss-license
 */
import ClassicEditor from '@ckeditor/ckeditor5-editor-classic/src/classiceditor.js';
import Bold from '@ckeditor/ckeditor5-basic-styles/src/bold.js';
import BlockQuote from '@ckeditor/ckeditor5-block-quote/src/blockquote.js';
import Essentials from '@ckeditor/ckeditor5-essentials/src/essentials.js';
import FontBackgroundColor from '@ckeditor/ckeditor5-font/src/fontbackgroundcolor.js';

import FontColor from '@ckeditor/ckeditor5-font/src/fontcolor.js';

import FontFamily from '@ckeditor/ckeditor5-font/src/fontfamily.js';
import FontSize from '@ckeditor/ckeditor5-font/src/fontsize.js';
import Heading from '@ckeditor/ckeditor5-heading/src/heading.js';
import Italic from '@ckeditor/ckeditor5-basic-styles/src/italic.js';
import Indent from '@ckeditor/ckeditor5-indent/src/indent.js';

import List from '@ckeditor/ckeditor5-list/src/list.js';
import Paragraph from '@ckeditor/ckeditor5-paragraph/src/paragraph.js';
import Strikethrough from '@ckeditor/ckeditor5-basic-styles/src/strikethrough.js';
import Subscript from '@ckeditor/ckeditor5-basic-styles/src/subscript.js';
import Superscript from '@ckeditor/ckeditor5-basic-styles/src/superscript.js';
import Table from '@ckeditor/ckeditor5-table/src/table.js';
import TableToolbar from '@ckeditor/ckeditor5-table/src/tabletoolbar';
import TableProperties from '@ckeditor/ckeditor5-table/src/tableproperties';
import TableCellProperties from '@ckeditor/ckeditor5-table/src/tablecellproperties';
import TableCaption from '@ckeditor/ckeditor5-table/src/tablecaption';
import Underline from '@ckeditor/ckeditor5-basic-styles/src/underline.js';
import WordCount from '@ckeditor/ckeditor5-word-count/src/wordcount.js';
import MathType from '@wiris/mathtype-ckeditor5/src/plugin';
import AutoSave from '@ckeditor/ckeditor5-autosave/src/autosave.js';
import SpecialCharacters from '@ckeditor/ckeditor5-special-characters/src/specialcharacters';
import SimpleUploadAdapter from '@ckeditor/ckeditor5-upload/src/adapters/simpleuploadadapter';
import Image from '@ckeditor/ckeditor5-image/src/image';
import ImageToolbar from '@ckeditor/ckeditor5-image/src/imagetoolbar';
import ImageCaption from '@ckeditor/ckeditor5-image/src/imagecaption';
import ImageStyle from '@ckeditor/ckeditor5-image/src/imagestyle';
import ImageResize from '@ckeditor/ckeditor5-image/src/imageresize';
import ImageUpload from '@ckeditor/ckeditor5-image/src/imageupload.js';
import RemoveFormat from '@ckeditor/ckeditor5-remove-format/src/removeformat.js';
import WProofreader from "@webspellchecker/wproofreader-ckeditor5/src/wproofreader";
import PasteFromOffice from "@ckeditor/ckeditor5-paste-from-office/src/pastefromoffice";
import Plugin from '@ckeditor/ckeditor5-core/src/plugin';
import ButtonView from '@ckeditor/ckeditor5-ui/src/button/buttonview';

import Comments from '@ckeditor/ckeditor5-comments/src/comments';
import BlockToolbar from '@ckeditor/ckeditor5-ui/src/toolbar/block/blocktoolbar';


class Completion extends Plugin {
    init() {
        const editor = this.editor;
        // The button must be registered among the UI components of the editor
        // to be displayed in the toolbar.
        editor.ui.componentFactory.add('completion', () => {
            // The button will be an instance of ButtonView.
            const button = new ButtonView();

            button.set({
                label: 'Completion',
                icon: '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"><g fill="none" fill-rule="evenodd"><circle cx="10" cy="10" r="10" fill="#004DF5"/><g stroke="#FFF" stroke-linecap="round" stroke-width="2"><path d="M5 10h10M10 15V5"/></g></g></svg>',

            });

            button.on('execute', () => {
                editor.model.change(writer => {
                    let selection = '';
                    let range = editor.model.document.selection.getFirstRange()
                    for(const value of range.getItems()){
                        selection = selection + value.data;
                    }

                    let firstChar = selection[0];
                    let lastChar = selection[selection.length - 1];

                    if (firstChar == " ") {
                        firstChar = " [";
                    } else {
                        firstChar = "[";
                    }
                    if (lastChar == " ") {
                        lastChar = "] ";
                    } else {
                        lastChar = "]";
                    }

                    editor.model.insertContent(
                        writer.createText(firstChar + selection + lastChar)
                    );
                });
            });

            return button;
        });
    }
}

class Selection extends Plugin {
    init() {
        const editor = this.editor;
        // The button must be registered among the UI components of the editor
        // to be displayed in the toolbar.
        editor.ui.componentFactory.add('selection', () => {
            // The button will be an instance of ButtonView.
            const button = new ButtonView();

            button.set({
                label: 'Selection',
                icon: '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"><g fill="none" fill-rule="evenodd"><circle cx="10" cy="10" r="10" fill="#004DF5"/><g stroke="#FFF" stroke-linecap="round" stroke-width="2"><path d="M5 10h10M10 15V5"/></g></g></svg>',
            });
            button.on('execute', () => {
                let lw = livewire.find(document.getElementById('cms').getAttribute('wire:id'));
                lw.set('showSelectionOptionsModal', true)
                // Create the event
                var event = new CustomEvent("initwithselection");

                window.dispatchEvent(event);
            });

            return button;
        });


    }
}

function SpecialCharactersTLC(editor) {
    editor.plugins.get('SpecialCharacters').addItems('Vreemde tekens', [
        {title: 'Ringel S', character: 'ß'},
        {title: 'O umlaut', character: 'ö'},
        {title: 'U umlaut', character: 'ü'},
        {title: 'A umlaut', character: 'ä'},
        {title: 'O accent circonflexe', character: 'ô️'},
        {title: 'U accent circonflexe', character: 'û'},
        {title: 'E accent circonflexe', character: 'ê️'},
        {title: 'A accent circonflexe', character: 'â'},
        {title: 'I accent circonflexe', character: 'î️'},
        {title: 'O accent aigu', character: 'ó'},
        {title: 'U accent aigu', character: 'ú'},
        {title: 'E accent aigu', character: 'é️'},
        {title: 'A accent aigu', character: 'á'},
        {title: 'I accent aigu', character: 'í️'},
        {title: 'O accent grave', character: 'ò'},
        {title: 'U accent grave', character: 'ù'},
        {title: 'E accent grave', character: 'è️'},
        {title: 'A accent grave', character: 'à'},
        {title: 'I accent grave', character: 'ì️'}
    ]);
}


class Editor extends ClassicEditor {
}

// Plugins to include in the build.
Editor.builtinPlugins = [
    AutoSave,
    Bold,
    BlockQuote,
    Essentials,
    FontFamily,
    FontSize,
    FontBackgroundColor,
    Heading,
    Image,
    ImageCaption,
    ImageResize, //LinkImage
    ImageStyle,
    ImageToolbar,
    ImageUpload,
    Indent,
    Italic,
    List,
    MathType,
    Paragraph,
    SimpleUploadAdapter,
    SpecialCharacters,
    SpecialCharactersTLC,
    Strikethrough,
    FontColor,
    Subscript,
    Superscript,
    BlockQuote,
    Table,
    TableCaption,
    TableCellProperties,
    TableProperties,
    TableToolbar,
    Underline,
    RemoveFormat,
    PasteFromOffice,
    WordCount,
    // WProofreader,
    Completion,
    Selection,
    Comments,
    BlockToolbar
];

// Editor configuration.
Editor.defaultConfig = {
    toolbar: {
        items: [
            'comment', '|',
            'completion',
            'selection',
            'bold',
            'italic',
            'underline',
            'strikethrough',
            'subscript',
            'superscript',
            'bulletedList',
            'numberedList',
            'blockQuote',
            '|',
            'outdent',
            'indent',
            '|',
            'insertTable',
            'fontFamily',
            'fontBackgroundColor',
            'fontSize',
            'undo',
            'redo',
            'MathType',
            'ChemType',
            'imageUpload',
            'specialCharacters',
            'fontColor',
            'heading',
            'removeFormat',
            // 'wproofreader',
        ]
    },
    language: 'nl',
    table: {
        contentToolbar: [
            'tableColumn', 'tableRow', 'mergeTableCells',
            'tableProperties', 'tableCellProperties', 'toggleTableCaption'
        ],
        tableProperties: {
            // ...
        },
        tableCellProperties: {
            // ...
        }
    },
    fontFamily: {
        options: [
            'default',
            'Zilla slab, serif',
            'Oswald, sans-serif',
            'Shantell Sans, sans-serif',
        ]
    },
    fontSize: {
        options: [['1', '1.000em'], ['2', '1.1250em'], ['3', '1.250em'], ['4', '1.375em'], ['5', '1.4375em'], ['6', '1.5em'], ['7', '1.625em'], ['8', '1.750em'], ['9', '2.250em'], ['10', '3em']].map(function (val) {
            return {
                model: val[1],
                title: val[0],
                view: {
                    name: 'span',
                    styles: {
                        'font-size': val[1]
                    }
                }
            };
        }),
        supportAllValues: true
    },
    heading: {
        options: [
            { model: 'paragraph', title: 'Paragraph'},
            { model: 'heading1', view: 'h1', title: 'Heading 1'},
            { model: 'heading2', view: 'h2', title: 'Heading 2'},
            { model: 'heading3', view: 'h4', title: 'Heading 3'},
        ]
    },

    ui: {
        viewportOffset: {top: 137}
    },

    comments: {
        editorConfig: {
            toolbar: {
                items: [
                    'bold',
                    'italic',
                    'underline',
                    'strikethrough',
                    'subscript',
                    'superscript',
                ]
            },
            extraPlugins: [ Bold, Italic, Underline ]
        }
    }
};

export default Editor;
