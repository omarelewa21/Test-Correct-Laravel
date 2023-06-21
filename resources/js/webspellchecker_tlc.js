WebspellcheckerTlc = {

    forTeacherQuestion: function(editor, language,wsc = true) {
            if(!wsc){
                return;
            }
            WebspellcheckerTlc.initWsc(editor,language);
            editor.on('resize', function (event) {
                WebspellcheckerTlc.triggerWsc(editor,language);
            });
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
                container: editor.ui.getEditableElement('main'),
                spellcheckLang: language,
                localization: 'nl'
            });
            instance.subscribe('problemCheckEnded', (event) => {
                window.dispatchEvent(new CustomEvent('wsc-problems-count-updated-'+editor.sourceElement.id, {
                    detail: { problemsCount: instance.getProblemsCount()}
                }));
            });
            try {
                instance.setLang(language);
            }catch (e) {
                console.dir(e);
            }
        }, 1000);
    },

    /**
     * This function is used to handle the spellchecker on/off button and store it in user session 
     * @param {object} editor
     */
    handleSpellCheckerOnOff: function(editor, initialStatus=true){
        spellChecker = editor.plugins.get('WProofreader');
        spellChecker.isEnabled = initialStatus;         // set initial status
        this.captureSpellCheckerOnOff(spellChecker);
    },
    captureSpellCheckerOnOff: function(spellChecker){
        currentState = spellChecker.isEnabled;
        spellChecker.on('change', () => {
            if(spellChecker.isEnabled != currentState){
                currentState = spellChecker.isEnabled;
                this.storeIsSpellCheckerOnOffInSession(currentState);
            }
        });
    },
    storeIsSpellCheckerOnOffInSession: function(isSpellCheckerEnabled){
        window.dispatchEvent(
            new CustomEvent('store-to-session', {'detail': {
                isSpellCheckerEnabled: isSpellCheckerEnabled
            }})
        );
    }
}