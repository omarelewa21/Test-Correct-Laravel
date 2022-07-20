WebspellcheckerTlc = {

    forTeacherQuestion: function(editor, language) {
            WEBSPELLCHECKER.init({
                container: editor.window.getFrame() ? editor.window.getFrame().$ : editor.element.$,
                spellcheckLang: language,
                localization: 'nl'
            });
        },
    lang: function(editor, language) {
            var config = { attributes: true, childList: false, subtree: false };
            var element = editor.ui.view.editable.element;
            var callback = function(mutationsList, observer){
                WEBSPELLCHECKER.getInstances().forEach(function(instance){
                    instance.setLang(language);
                });
            }
            var observer = new MutationObserver(callback);
            observer.observe(element, config);
        }

}