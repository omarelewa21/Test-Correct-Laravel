import View from "../ckeditor5/node_modules/@ckeditor/ckeditor5-ui/src/view.js";

class CkEditor5Button extends View {
    constructor( locale ) {
        super( locale );
        const bind = this.bindTemplate;

        // Views define their interface (state) using observable attributes.
        this.set( 'classList');
        this.set( 'label');
        this.set( 'type', 'button' );
        this.set( 'eventName' );


        this.setTemplate( {
            tag: 'button',

            // The element of the view can be defined with its children.
            children: [
                {
                    tag: 'span',
                    children: [
                        {
                            text: bind.to('label') ?? ''
                        }
                    ]
                }
            ],
            attributes: {
                class: [
                    'button button-gradient space-x-2.5 focus:outline-none',
                    bind.if( 'isEnabled', 'button-disabled', value => !value ),

                    // Observable attributes control the state of the view in DOM.
                    bind.to( 'classList' )
                ],
            },
            on: {
                mousedown: bind.to( evt => {
                    evt.preventDefault();
                } ),

                click: bind.to(evt => {
                    this.element.parentElement.dispatchEvent(new CustomEvent('button-' + this.eventName + '-clicked'));
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

export default CkEditor5Button;