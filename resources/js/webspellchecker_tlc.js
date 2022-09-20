WebspellcheckerTlc = {

    forTeacherQuestion: function(editor, language,wsc = true) {
            if(!wsc){
                return;
            }
            WebspellcheckerTlc.initWsc(editor,language);
            editor.on('resize', function (event) {
                WebspellcheckerTlc.triggerWsc(editor,language);
            });
            editor.focus();
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
    },
    triggerWsc: function(editor,language){
        if(editor.element.$.parentNode.getElementsByClassName('wsc_badge').length==0){
            WebspellcheckerTlc.initWsc(editor,language);
        }
    },
    initWsc: function(editor,language){
        setTimeout(function () {
            var instance = WEBSPELLCHECKER.init({
                container: editor.window.getFrame() ? editor.window.getFrame().$ : editor.element.$,
                spellcheckLang: language,
                localization: 'nl'
            });
            try {
                instance.setLang(language);
            }catch (e) {
                console.dir(e);
            }
        }, 1000);
    }

}