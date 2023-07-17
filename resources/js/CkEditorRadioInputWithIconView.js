import View from "../ckeditor5/node_modules/@ckeditor/ckeditor5-ui/src/view.js";

class CkEditorRadioWithIcon extends View {
    constructor( locale ) {
        super( locale );
        const bind = this.bindTemplate;

        // Views define their interface (state) using observable attributes.
        this.set( 'spanClass', 'emoji-picker-circle');
        this.set( 'labelClass');
        this.set( 'inputClass');

        this.set( 'inputName', 'new-comment-emoji');

        this.set( 'emojiValue');
        this.set( 'iconName');

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
                        'data-emoji': [
                            bind.to('emojiValue')
                        ],
                        'data-iconname': [
                            bind.to('iconName')
                        ],
                    }
                },
                {
                    tag: 'span',
                    attributes: {
                        class: [
                            'emoji-picker-circle',
                            bind.to( 'spanClass' ),
                        ],
                    }
                },
            ],
            attributes: {
                class: [
                    'emoji-picker-radio emoji-picker-radio-container',
                    bind.to( 'labelClass' )
                ],
                tabindex: -1,
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

export default CkEditorRadioWithIcon;