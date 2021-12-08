CKEDITOR.plugins.add( 'readspeaker', {
    icons: 'readspeaker',
    init: function( editor ) {
        editor.addCommand( 'readContent', {
            exec: function( editor ) {
                if(document.getElementsByClassName('rsbtn_tooltoggle').length==0){
                    return;
                }
                if(document.getElementsByClassName('rsicn-click-listen').length==0){
                    document.getElementsByClassName('rsbtn_tooltoggle')[0].click();
                }
                var parentNode = document.getElementsByClassName('rsicn-click-listen')[0].parentNode;
                if(!parentNode.classList.contains('active')){
                    parentNode.click();
                }
                var oldEl = document.getElementById('there_can_only_be_one');
                if(oldEl){
                    oldEl.remove();
                }
                var element = document.getElementById('hidden_span_'+editor.name),
                    clone = element.cloneNode(true); // true means clone all childNodes and all event handlers
                clone.id = "there_can_only_be_one";
                clone.classList.remove('hidden');
                clone.classList.add('rs-click-listen');
                var target = document.getElementById(editor.id+'_contents');
                target.appendChild(clone);
                document.getElementsByClassName('cke_wysiwyg_frame')[0].classList.add('hidden');
                document.getElementsByClassName('rs-click-listen')[0].click();
            }
        });
        editor.ui.addButton( 'readContent', {
            label: 'Deze nog vertalen',
            command: 'readContent',
            toolbar: 'readspeaker_toolbar'
        });
    }
});