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
                icon: '<svg viewBox="0 0 68 64" xmlns="http://www.w3.org/2000/svg"><g fill="none" fill-rule="evenodd"><path d="M43.71 11.025a11.508 11.508 0 0 0-1.213 5.159c0 6.42 5.244 11.625 11.713 11.625.083 0 .167 0 .25-.002v16.282a5.464 5.464 0 0 1-2.756 4.739L30.986 60.7a5.548 5.548 0 0 1-5.512 0L4.756 48.828A5.464 5.464 0 0 1 2 44.089V20.344c0-1.955 1.05-3.76 2.756-4.738L25.474 3.733a5.548 5.548 0 0 1 5.512 0l12.724 7.292z" fill="#FFF"/><path d="M45.684 8.79a12.604 12.604 0 0 0-1.329 5.65c0 7.032 5.744 12.733 12.829 12.733.091 0 .183-.001.274-.003v17.834a5.987 5.987 0 0 1-3.019 5.19L31.747 63.196a6.076 6.076 0 0 1-6.037 0L3.02 50.193A5.984 5.984 0 0 1 0 45.003V18.997c0-2.14 1.15-4.119 3.019-5.19L25.71.804a6.076 6.076 0 0 1 6.037 0L45.684 8.79zm-29.44 11.89c-.834 0-1.51.671-1.51 1.498v.715c0 .828.676 1.498 1.51 1.498h25.489c.833 0 1.51-.67 1.51-1.498v-.715c0-.827-.677-1.498-1.51-1.498h-25.49.001zm0 9.227c-.834 0-1.51.671-1.51 1.498v.715c0 .828.676 1.498 1.51 1.498h18.479c.833 0 1.509-.67 1.509-1.498v-.715c0-.827-.676-1.498-1.51-1.498H16.244zm0 9.227c-.834 0-1.51.671-1.51 1.498v.715c0 .828.676 1.498 1.51 1.498h25.489c.833 0 1.51-.67 1.51-1.498v-.715c0-.827-.677-1.498-1.51-1.498h-25.49.001zm41.191-14.459c-5.835 0-10.565-4.695-10.565-10.486 0-5.792 4.73-10.487 10.565-10.487C63.27 3.703 68 8.398 68 14.19c0 5.791-4.73 10.486-10.565 10.486v-.001z" fill="#1EBC61" fill-rule="nonzero"/><path d="M60.857 15.995c0-.467-.084-.875-.251-1.225a2.547 2.547 0 0 0-.686-.88 2.888 2.888 0 0 0-1.026-.531 4.418 4.418 0 0 0-1.259-.175c-.134 0-.283.006-.447.018-.15.01-.3.034-.446.07l.075-1.4h3.587v-1.8h-5.462l-.214 5.06c.319-.116.682-.21 1.089-.28.406-.071.77-.107 1.088-.107.218 0 .437.021.655.063.218.041.413.114.585.218s.313.244.422.419c.109.175.163.391.163.65 0 .424-.132.745-.396.961a1.434 1.434 0 0 1-.938.325c-.352 0-.656-.1-.912-.3-.256-.2-.43-.453-.523-.762l-1.925.588c.1.35.258.664.472.943.214.279.47.514.767.706.298.191.63.339.995.443.365.104.749.156 1.151.156.437 0 .86-.064 1.272-.193.41-.13.778-.323 1.1-.581a2.8 2.8 0 0 0 .775-.981c.193-.396.29-.864.29-1.405h-.001z" fill="#FFF" fill-rule="nonzero"/></g></svg>',

            });

            button.on('execute', () => {
                editor.model.change(writer => {
                    let selection = editor.data.stringify(editor.model.getSelectedContent(editor.model.document.selection));
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
                icon: '<svg viewBox="0 0 68 64" xmlns="http://www.w3.org/2000/svg"><g fill="none" fill-rule="evenodd"><path d="M43.71 11.025a11.508 11.508 0 0 0-1.213 5.159c0 6.42 5.244 11.625 11.713 11.625.083 0 .167 0 .25-.002v16.282a5.464 5.464 0 0 1-2.756 4.739L30.986 60.7a5.548 5.548 0 0 1-5.512 0L4.756 48.828A5.464 5.464 0 0 1 2 44.089V20.344c0-1.955 1.05-3.76 2.756-4.738L25.474 3.733a5.548 5.548 0 0 1 5.512 0l12.724 7.292z" fill="#FFF"/><path d="M45.684 8.79a12.604 12.604 0 0 0-1.329 5.65c0 7.032 5.744 12.733 12.829 12.733.091 0 .183-.001.274-.003v17.834a5.987 5.987 0 0 1-3.019 5.19L31.747 63.196a6.076 6.076 0 0 1-6.037 0L3.02 50.193A5.984 5.984 0 0 1 0 45.003V18.997c0-2.14 1.15-4.119 3.019-5.19L25.71.804a6.076 6.076 0 0 1 6.037 0L45.684 8.79zm-29.44 11.89c-.834 0-1.51.671-1.51 1.498v.715c0 .828.676 1.498 1.51 1.498h25.489c.833 0 1.51-.67 1.51-1.498v-.715c0-.827-.677-1.498-1.51-1.498h-25.49.001zm0 9.227c-.834 0-1.51.671-1.51 1.498v.715c0 .828.676 1.498 1.51 1.498h18.479c.833 0 1.509-.67 1.509-1.498v-.715c0-.827-.676-1.498-1.51-1.498H16.244zm0 9.227c-.834 0-1.51.671-1.51 1.498v.715c0 .828.676 1.498 1.51 1.498h25.489c.833 0 1.51-.67 1.51-1.498v-.715c0-.827-.677-1.498-1.51-1.498h-25.49.001zm41.191-14.459c-5.835 0-10.565-4.695-10.565-10.486 0-5.792 4.73-10.487 10.565-10.487C63.27 3.703 68 8.398 68 14.19c0 5.791-4.73 10.486-10.565 10.486v-.001z" fill="#1EBC61" fill-rule="nonzero"/><path d="M60.857 15.995c0-.467-.084-.875-.251-1.225a2.547 2.547 0 0 0-.686-.88 2.888 2.888 0 0 0-1.026-.531 4.418 4.418 0 0 0-1.259-.175c-.134 0-.283.006-.447.018-.15.01-.3.034-.446.07l.075-1.4h3.587v-1.8h-5.462l-.214 5.06c.319-.116.682-.21 1.089-.28.406-.071.77-.107 1.088-.107.218 0 .437.021.655.063.218.041.413.114.585.218s.313.244.422.419c.109.175.163.391.163.65 0 .424-.132.745-.396.961a1.434 1.434 0 0 1-.938.325c-.352 0-.656-.1-.912-.3-.256-.2-.43-.453-.523-.762l-1.925.588c.1.35.258.664.472.943.214.279.47.514.767.706.298.191.63.339.995.443.365.104.749.156 1.151.156.437 0 .86-.064 1.272-.193.41-.13.778-.323 1.1-.581a2.8 2.8 0 0 0 .775-.981c.193-.396.29-.864.29-1.405h-.001z" fill="#FFF" fill-rule="nonzero"/></g></svg>',
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
    WProofreader,
    Completion,
    Selection
];

// Editor configuration.
Editor.defaultConfig = {
    toolbar: {
        items: [
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
            'completion',
            'selection',
            'wproofreader',
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
            'Arial',
            'Calibri',
            'Times New Roman',
            'Verdana'
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
    ui: {
        viewportOffset: {top: 70}
    }
};

export default Editor;
