CKEDITOR.plugins.add( 'readspeaker', {
    icons: 'readspeaker',
    init: function( editor ) {
        editor.addCommand( 'readContent', {
            exec: function( editor ) {
                if(typeof rspkr == "undefined"){
                    return;
                }
                rspkr.cke_play_started = false;
                rspkr.ui.Tools.ClickListen.activate();
                removeOldElement();
                var node = cloneHiddenSpan(editor);
                node.classList.add('rs-click-listen');
                removeSelectionFromEditor(editor);
                document.getElementsByClassName('cke_wysiwyg_frame')[0].classList.add('hidden');
                document.getElementsByClassName('cke_wysiwyg_frame')[0].classList.add('readspeaker_hidden_element');
                document.getElementsByClassName('rs-click-listen')[0].click();

            }
        });
        editor.ui.addButton( 'readContent', {
            label: 'Deze nog vertalen',
            command: 'readContent',
            toolbar: 'readspeaker_toolbar',
            icon: this.path + 'icons/readContent.png'
        });
    }
});