/**
 * @license Copyright (c) 2014-2022, CKSource Holding sp. z o.o. All rights reserved.
 * For licensing, see LICENSE.md or https://ckeditor.com/legal/ckeditor-oss-license
 */
import ClassicEditor from '@ckeditor/ckeditor5-editor-classic/src/classiceditor.js';
import Bold from '@ckeditor/ckeditor5-basic-styles/src/bold.js';
import Essentials from '@ckeditor/ckeditor5-essentials/src/essentials.js';
import FontFamily from '@ckeditor/ckeditor5-font/src/fontfamily.js';
import FontSize from '@ckeditor/ckeditor5-font/src/fontsize.js';
import Italic from '@ckeditor/ckeditor5-basic-styles/src/italic.js';
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

class Editor extends ClassicEditor {}

// Plugins to include in the build.
Editor.builtinPlugins = [
	Bold,
	Essentials,
	FontFamily,
	FontSize,
	Italic,
	List,
	Paragraph,
	Strikethrough,
	Subscript,
	Superscript,
	Table,
	TableToolbar,
	TableProperties,
	TableCellProperties,
	TableCaption,
	Underline,
	WordCount,
	MathType,
	AutoSave
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
			'|',
			'insertTable',
			'fontFamily',
			'fontSize',
			'undo',
			'redo',
			'MathType',
			'ChemType'
		]
	},
	language: 'nl',
	table: {
		contentToolbar: [
			'tableColumn', 'tableRow', 'mergeTableCells',
			'tableProperties', 'tableCellProperties','toggleTableCaption'
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
		options: [['1','1.000em'], ['2','1.1250em'], ['3','1.250em'], ['4','1.375em'], ['5','1.4375em'], ['6','1.5em'], ['7','1.625em'], ['8','1.750em'], ['9','2.250em'], ['10','3em']].map(function(val){
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
		viewportOffset: { top: 70 }
	}
};

export default Editor;
