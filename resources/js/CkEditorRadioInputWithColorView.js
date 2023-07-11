import View from "../ckeditor5/node_modules/@ckeditor/ckeditor5-ui/src/view.js";

class CkEditorRadioWithColor extends View {
    constructor( locale ) {
        super( locale );
        const bind = this.bindTemplate;

        // Views define their interface (state) using observable attributes.
        this.set( 'spanClass', 'color-picker-circle');
        this.set( 'labelClass');
        this.set( 'inputClass');

        this.set( 'inputName', 'new-comment-color');
        this.set( 'rgb');
        this.set( 'colorName');

        this.setTemplate( {
            tag: 'label',

            // The element of the view can be defined with its children.
            children: [
                {
                    tag: 'input',
                    attributes: {
                        type: ['radio'],
                        name: [
                            bind.to('inputName')
                        ],
                        class: [
                            bind.to( 'inputClass' ),
                        ],
                        'data-color': [
                            bind.to('colorName')
                        ],
                    }
                },
                {
                    tag: 'span',
                    attributes: {
                        class: [
                            bind.to( 'spanClass' ),
                        ],
                        style: [
                            bind.to( 'rgb', value => value ? 'background-color: rgb(' + value + ');' : '' ),
                        ]
                    }
                },
            ],
            attributes: {
                class: [
                    'color-picker-radio color-picker-radio-container',
                    bind.to( 'labelClass' )
                ],
            },
            on: {
                mousedown: bind.to( evt => {
                    evt.preventDefault();
                } ),

                click: bind.to(evt => {
                    window.dispatchEvent(
                        new CustomEvent( this.inputName + '-updated', {detail: {color: 'rgba(' + this.rgb + ', 0.4)'}})
                    );
                    this.element.querySelector('input').checked = true;
                }),
            }
        } );
    }

    /**
     * Focuses the {@link #element} of the button.
     */
    focus() {
        this.element.focus();
    }
}

export default CkEditorRadioWithColor;