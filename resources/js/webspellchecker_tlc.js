WebspellcheckerTlc = {

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
    subscribeToProblemCounter: function (editor) {
        let i = 0;
        let problemTimer = setInterval(function() {
            ++i;
            if (i === 50) clearInterval(problemTimer);
            if(typeof WEBSPELLCHECKER != "undefined"){
                let instance = WEBSPELLCHECKER.getInstances().pop();
                if(instance == undefined) return;
                instance.subscribe('problemCheckEnded', (event) => {
                    window.dispatchEvent(new CustomEvent('wsc-problems-count-updated-'+editor.sourceElement.id, {
                        detail: { problemsCount: instance.getProblemsCount()}
                    }));
                });
                clearInterval(problemTimer);
            }
        }, 200);
    },

    /**
     * This function is used to handle the spellchecker on/off button and store it in user session 
     * @param {object} editor
     */
    handleSpellCheckerOnOff: function(editor, initialStatus=true){
        if(!editor.plugins.has('WProofreader')) return;
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