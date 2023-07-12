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
    }
}