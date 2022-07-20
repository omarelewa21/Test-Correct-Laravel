WebspellcheckerTlc = function(){
    init = function() {
        function forTeacherQuestion(editor, language) {
            WEBSPELLCHECKER.init({
                container: editor.window.getFrame() ? editor.window.getFrame().$ : editor.element.$,
                spellcheckLang: language,
                localization: 'nl'
            });
        }
        return {
            forTeacherQuestion
        }
    }
    return {
        init
    }
}