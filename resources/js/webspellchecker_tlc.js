WebspellcheckerTlc = {

    forTeacherQuestion: function(editor, language,wsc = true) {
            if(!wsc){
                return;
            }
            var instance = WEBSPELLCHECKER.init({
                container: editor.window.getFrame() ? editor.window.getFrame().$ : editor.element.$,
                spellcheckLang: language,
                localization: 'nl'
            });
            instance.setLang(language);
        },
    lang: function(editor, language) {
        var i = 0;
        var timer = setInterval(function() {
            ++i;
            if (i === 50) clearInterval(timer);
            if(typeof WEBSPELLCHECKER != "undefined"){
                WEBSPELLCHECKER.getInstances().forEach(function(instance){
                    instance.setLang(language);
                });
                clearInterval(timer);
            }
        }, 200);
    },
    setEditorToReadOnly: function(editor) {
        setTimeout(function(){editor.ui.view.editable.element.setAttribute('contenteditable',false)},3000);
    }

}