ReadSpeaker.q(function() {
    console.log('rs_tlc_skin initialized!');
});
window.rsConf = {
    general: {
        usePost: true,
        skipHiddenContent:true
    },
    ui: {
        scrollcontrols: {
            vertical : 'bottom',
            horizontal: 'left'
        }
    },
    cb: {
        ui: {
            beforeclose: function(){
                var focusedElements = document.getElementsByClassName('rs-cl-tabbable');
                if(typeof focusedElements.length == "undefined" || focusedElements.length==0){
                    return;
                }
                focusedElements[0].classList.remove('rs-cl-tabbable');
            },
            close: function() {
                console.log('Player closed and callback fired!');
                var oldEl = document.getElementById('there_can_only_be_one');
                var playerStarted = (typeof rspkr.cke_play_started != "undefined")?rspkr.cke_play_started:false;
                if(oldEl && playerStarted){
                    oldEl.remove();
                    if(document.getElementsByClassName('readspeaker_hidden_element')){
                        document.getElementsByClassName('readspeaker_hidden_element')[0].classList.remove('hidden');
                    }
                }
            },
            play: function() {
                console.log('Play callback fired!');
                rspkr.cke_play_started = true;
            }
        }
    }
};

function handleFocusForReadspeaker()
{
    //if clickListen is activated you cannot type an L in a textfield
    rspkr.ui.Tools.ClickListen.deactivate();
}
function handleBlurForReadspeaker()
{
    rspkr.ui.Tools.ClickListen.activate();
}

function handleMouseupForReadspeaker(id)
{
    console.dir(id);
}

function readCkEditorOnSelect(editor)
{
    if(typeof rspkr == "undefined"){
        return;
    }
    rspkr.cke_play_started = false;
    rspkr.ui.Tools.ClickListen.activate();
    removeOldElement();
    var node = cloneHiddenSpan(editor);
    setSelectedElement(node,editor);
    removeSelectionFromEditor(editor);
    document.getElementsByClassName('cke_wysiwyg_frame')[0].classList.add('hidden');
    document.getElementsByClassName('cke_wysiwyg_frame')[0].classList.add('readspeaker_hidden_element');
    document.getElementsByClassName('rs-click-listen')[0].click();
}

function removeSelectionFromEditor(editor) {
    var range = new CKEDITOR.dom.range(editor.document);
    var body = editor.document.getBody();
    range.setStart(body, 0);
    range.setEnd(body, 0);
    editor.getSelection().selectRanges([range]);
}

function removeOldElement()
{
    var oldEl = document.getElementById('there_can_only_be_one');
    if(oldEl){
        oldEl.remove();
    }
}

function cloneHiddenSpan(editor)
{
    var element = document.getElementById('hidden_span_'+editor.name);
    var clone = element.cloneNode(true);
    clone.id = "there_can_only_be_one";
    clone.classList.remove('hidden');
    var target = document.getElementById(editor.id+'_contents');
    target.appendChild(clone);
    return clone;
}

function setSelectedElement(node,editor)
{
    var xpath = './/*[contains(text(),"'+editor.document.getSelection().getSelectedText()+'")]';
    var matchingElement = document.evaluate(xpath, node, null, XPathResult.FIRST_ORDERED_NODE_TYPE, null).singleNodeValue;
    if(matchingElement != null){
        matchingElement.classList.add('rs-click-listen');
        return;
    }
    node.classList.add('rs-click-listen');
}

function readTextArea(questionId)
{
    rspkr.cke_play_started = false;
    removeOldElement();
    var textarea = document.getElementById('textarea_'+questionId);
    var hidden_div = document.createElement('div');
    hidden_div.id = 'there_can_only_be_one';
    hidden_div.innerHTML = textarea.value;
    hidden_div.style.height = textarea.offsetHeight+'px';
    hidden_div.classList.add('rs-click-listen');
    textarea.parentNode.insertBefore(hidden_div,textarea);
    textarea.classList.add('hidden');
    textarea.classList.add('readspeaker_hidden_element');
    hidden_div.click();
}