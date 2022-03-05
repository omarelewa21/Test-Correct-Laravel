ReadSpeaker.q(function() {
    console.log('rs_tlc_skin initialized!');
    rspkr.rs_tlc_play_started = false;
    if(!rspkr.mobile()){
        rspkr.tlc_clicklisten_active = rspkr.ui.Tools.ClickListen.active();
    }
    rspkr.rs_tlc_prevent_close = false;
    rspkr.rs_tlc_container = false;

});
window.rsConf = {
    general: {
        usePost: true,
        skipHiddenContent:true
    },
    ui: {
        scrollcontrols: {
            vertical : 'top',
            horizontal: 'left'
        },
        toolbar: {
            inverted : false
        },
        mobileVertPos: 'bottom=100'
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
                // var oldEl = document.getElementById('there_can_only_be_one');
                // var playerStarted = (typeof rspkr.rs_tlc_play_started != "undefined")?rspkr.rs_tlc_play_started:false;
                // if(oldEl && playerStarted){
                //     oldEl.remove();
                //     displayHiddenElements();
                //     window.getSelection().removeAllRanges();
                // }
                hideRsPlayer();
                window.document.dispatchEvent(new Event("readspeaker_closed", {
                    bubbles: true,
                    cancelable: true
                }));
            },
            stop: function() {
                setMobileClasses('stop');
                console.log('Player stopped and callback fired!');
                if(typeof rspkr.tlc_clicklisten_active=='undefined'){
                    rspkr.ui.getActivePlayer().close();
                }
                if(rspkr.tlc_clicklisten_active){
                    return rspkr.ui.Tools.ClickListen.activate();
                }
                rspkr.ui.Tools.ClickListen.deactivate();
                rspkr.ui.getActivePlayer().close();
            },
            open: function() {
                console.log('Open callback fired!');
                window.document.dispatchEvent(new Event("readspeaker_opened", {
                    bubbles: true,
                    cancelable: true
                }));
            },
            play: function() {
                console.log('Play callback fired!');
                setMobileClasses('play');
                rspkr.rs_tlc_play_started = true;
                rspkr.rs_tlc_prevent_close = false;
                showRsPlayer();
                window.document.dispatchEvent(new Event("readspeaker_started", {
                    bubbles: true,
                    cancelable: true
                }));
            },
            pause: function() {
                console.log('Pause callback fired!');
                setMobileClasses('pause');
                rspkr.rs_tlc_play_started = false;
            }
        }
    }
};


function startRsPlayer()
{
    showRsPlayer();
    var els = document.getElementsByClassName('rsplay');
    if(els){
        [].forEach.call(els, function (el) {
            el.click();
        });
        return;
    }
}

function handleFocusForReadspeaker()
{
    //if clickListen is activated you cannot type an L in a textfield
    rspkr.tlc_clicklisten_active = rspkr.ui.Tools.ClickListen.active();
    rspkr.ui.Tools.ClickListen.deactivate();
}
function handleBlurForReadspeaker()
{
    if(typeof rspkr.tlc_clicklisten_active == 'undefined'){
        return;
    }
    if(rspkr.tlc_clicklisten_active){
        rspkr.rs_tlc_play_started = false;
        rspkr.ui.Tools.ClickListen.activate();
    }
}
function handleClickListenToggle()
{
    if(typeof rspkr.tlc_clicklisten_active == "undefined"){
        return;
    }
    if(rspkr.tlc_clicklisten_active==rspkr.ui.Tools.ClickListen.active()){
        return;
    }
    if(rspkr.tlc_clicklisten_active){
        rspkr.ui.Tools.ClickListen.activate();
        return;
    }
    rspkr.ui.Tools.ClickListen.deactivate();
}


function handleMouseupForReadspeaker(e,obj)
{
    removeOldElement();
    rspkr.rs_tlc_play_started = false;
    if(doNotReadInput(e,obj)){
        return;
    }
    var matchingElement = e.toElement;
    var hidden_div = document.createElement('div');
    matchingElement.parentNode.insertBefore(hidden_div,matchingElement);
    hidden_div.id = 'there_can_only_be_one';
    hidden_div.innerHTML = getValueOfInput(e,obj);
    hidden_div.style.height = matchingElement.offsetHeight+'px';
    hidden_div.style.width = matchingElement.offsetWidth+'px';
    hidden_div.style.display = 'inline-flex';
    hidden_div.classList.add('rs-click-listen');
    hidden_div.classList.add('rs-shadow-input');
    hidden_div.classList.add('form-input');
    hidden_div.classList.add('overflow-ellipsis');
    matchingElement.classList.add('hidden');
    matchingElement.classList.add('readspeaker_hidden_element');
    rspkr.ui.Tools.ClickListen.activate();
    hidden_div.click();
}

function getValueOfInput(e,obj)
{
    if(obj.getSelection().toString()!=''){
        return obj.getSelection().toString();
    }
    if(e.toElement == ''  || e.toElement == null){
        return '';
    }
    return e.toElement.title;
}

function doNotReadInput(e,obj)
{
    if(e.toElement == ''  || e.toElement == null){
        return true;
    }
    try {
        if (e.toElement.matches(':-internal-autofill-selected') && e.toElement.title != '') {
            return false;
        }
    }catch(error){
        //silentfail
    }
    try {
        if(e.toElement.matches(':-webkit-autofill')&&e.toElement.title !=''){
            return false;
        }
    }catch(error){
        //silentfail
    }
    try {
        if(e.toElement.matches(':autofill')&&e.toElement.title !=''){
            return false;
        }
    }catch(error){
        //silentfail
    }
    if(obj.getSelection().toString()==''){
        return true;
    }
    return false;
}

function readCkEditorOnSelect(editor)
{
    if(typeof rspkr == "undefined"){
        return;
    }
    rspkr.rs_tlc_play_started = false;
    //rspkr.ui.Tools.ClickListen.activate();
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
function removeReadableElements()
{
    var elements = document.getElementsByClassName('readspeaker_readable_element');
    for (var i=0; i < elements.length; i++) {
        elements[i].remove();
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
    if(rspkr.rs_tlc_play_started){
        return;
    }
    rspkr.ui.Tools.ClickListen.activate();
    rspkr.rs_tlc_play_started = false;
    rspkr.rs_tlc_prevent_close = true;
    var hidden_div = createHiddenDivTextArea(questionId)
    hidden_div.click();
}

function createHiddenDivTextArea(questionId){

    var hidden_div = getHiddenDivForTextarea(questionId);
    var textarea = document.getElementById('textarea_'+questionId);
    var container = textarea.closest('.open-question-container');
    if(container){
        rspkr.rs_tlc_container = container;
    }
    hidden_div.id = 'there_can_only_be_one';
    hidden_div.innerHTML = textarea.value;
    hidden_div.style.height = textarea.offsetHeight+'px';
    hidden_div.style.width = textarea.offsetWidth+'px';
    hidden_div.classList.add('rs-shadow-textarea');
    hidden_div.classList.add('form-input');
    hidden_div.classList.add('overflow-ellipsis');

    hidden_div.classList.add('rs-click-listen');
    textarea.parentNode.insertBefore(hidden_div,textarea);
    textarea.classList.add('hidden');
    textarea.classList.add('readspeaker_hidden_element');
    return hidden_div;
}

function getHiddenDivForTextarea(questionId)
{
    var oldEl = document.getElementById('there_can_only_be_one');
    var possibleTextarea = false;
    var hidden_div;
    if(oldEl){
        possibleTextarea = oldEl.nextElementSibling;
    }
    if(possibleTextarea.id=='textarea_'+questionId){
        hidden_div = oldEl;
    }else{
        removeOldElement();
        hidden_div = document.createElement('div');
    }
    return hidden_div;
}

function showRsPlayer()
{
    hideByClassName('rs_starter_button');
    showById('readspeaker_button1');
}

function hideRsPlayer()
{
    if(rspkr.rs_tlc_prevent_close){
        return;
    }
    showByClassName('rs_starter_button');
    hideById('readspeaker_button1');
    rspkr.rs_tlc_play_started = false;
    displayHiddenElementsAndRemoveTheRest();
    window.getSelection().removeAllRanges();
}
function displayHiddenElementsAndRemoveTheRest()
{
    removeOldElement();
    removeReadableElementsAndDisplayHiddenElements();
}
function removeReadableElementsAndDisplayHiddenElements()
{
    if(document.getElementById('there_can_only_be_one')){
        return;
    }
    if(typeof rspkr.rs_tlc_play_started=='undefined'){
        return;
    }
    if(rspkr.rs_tlc_play_started){
        return;
    }
    removeHiddenDivsForContainer();
}

function showById(id)
{
    var element = document.getElementById(id);
    if(element){
        element.classList.remove('hidden');
    }
}

function hideById(id)
{
    var element = document.getElementById(id);
    if(element){
        element.classList.add('hidden');
    }
}

function showByClassName(class_name)
{
    var elements = document.getElementsByClassName(class_name);
    if(elements){
        [].forEach.call(elements, function (el) {
            el.classList.remove('hidden');
        });
    }
}

function hideByClassName(class_name)
{
    var elements = document.getElementsByClassName(class_name);
    if(elements){
        [].forEach.call(elements, function (el) {
            el.classList.add('hidden');
        });
    }
}

function disableContextMenuOnCkeditor()
{
    var element = document.getElementsByClassName('ck-editor__editable_inline')[0];
    if(element) {
        element.addEventListener("contextmenu", (evt, name, val) => {
            evt.preventDefault();
            return false;
        });
    }
}
function shouldNotReinitCkeditor(el)
{
    if(!checkElementInActiveQuestion(el)){
        return true;
    }
    return false;
}

function shouldNotCreateHiddenTextarea(id)
{
    var oldEl = document.getElementById('there_can_only_be_one');
    var possibleTextarea = false;
    var textareaId = 'textarea_'+id;
    var currentTextarea = document.querySelector('#'+textareaId);
    if(currentTextarea && !checkElementInActiveQuestion(currentTextarea)){
        return true;
    }
    if(oldEl){
        possibleTextarea = oldEl.nextElementSibling;
    }
    if(possibleTextarea && !checkElementInActiveQuestion(oldEl)){
        return true;
    }
    if(possibleTextarea && !checkPossibleTextAreaValid(possibleTextarea)){
        return true;
    }
    if(possibleTextarea && checkPossibleTextAreaAlreadyExists(possibleTextarea,textareaId)){
        return true;
    }
    return false;
}

function checkPossibleTextAreaValid(possibleTextarea)
{
    if(possibleTextarea == null){
        return false;
    }
    if(possibleTextarea.nodeName!='TEXAREA'){
        return false;
    }
    return true;
}
function checkPossibleTextAreaAlreadyExists(possibleTextarea,id)
{
    if(possibleTextarea.id==id) {
        return true;
    }
    return false;
}
function checkElementInActiveQuestion(el)
{
    var container = el.closest('.rs_readable');
    if(container){
        return true;
    }
    return false;
}

function shouldNotCreateHiddenDivsForTextboxesCompletion(containerId)
{
    return shouldNotCreateHiddenDivs(containerId);
}

function createHiddenDivsForTextboxesCompletion(containerId)
{
    var container = document.querySelector('#'+containerId);
    if(!container){
        return;
    }
    var inputs = container._x_refs;
    if(!inputs){
        return;
    }
    rspkr.rs_tlc_container = container;
    var inputsArray = Object.entries(inputs);
    for (var i=0; i < inputsArray.length; i++) {
        createHiddenDivForTextboxAndHideTextbox(inputsArray[i][1]);
    }
}

function shouldNotCreateHiddenDivsForSelects(containerId)
{
    return shouldNotCreateHiddenDivs(containerId);
}

function shouldNotCreateHiddenDivs(containerId)
{
    if(document.getElementById('there_can_only_be_one')){
        return true;
    }
    var element = document.querySelector('#'+containerId);
    if(element && !checkElementInActiveQuestion(element)){
        return true;
    }
    return false;
}

function createHiddenDivsForSelects(containerId)
{
    var container = document.querySelector('#'+containerId);
    if(!container){
        return;
    }
    var inputs = container._x_refs;
    if(!inputs){
        return;
    }
    rspkr.rs_tlc_container = container;
    var inputsArray = Object.entries(inputs);
    for (var i=0; i < inputsArray.length; i++) {
        createHiddenDivForSelectAndHideSelect(inputsArray[i][1]);
    }
}

function removeHiddenDivsForTextboxesCompletion(containerId)
{
    var container = document.querySelector('#'+containerId);
    if(!container){
        return;
    }
    var inputs = container._x_refs;
    if(!inputs){
        return;
    }
    var inputsArray = Object.entries(inputs);
    for (var i=0; i < inputsArray.length; i++) {
        removeHiddenDivForTextboxAndShowTextbox(inputsArray[i][1]);
    }
}
function removeHiddenDivsForSelect(containerId)
{
    var container = document.querySelector('#'+containerId);
    if(!container){
        return;
    }
    var inputs = container._x_refs;
    if(!inputs){
        return;
    }
    var inputsArray = Object.entries(inputs);
    for (var i=0; i < inputsArray.length; i++) {
        removeHiddenDivForSelectAndShowSelect(inputsArray[i][1]);
    }
}
function removeHiddenDivsForContainer()
{
    if(!rspkr.rs_tlc_container){
        return;
    }
    var inputs = rspkr.rs_tlc_container._x_refs;
    if(!inputs){
        return;
    }
    var inputsArray = Object.entries(inputs);
    for (var i=0; i < inputsArray.length; i++) {
        removeHiddenDivForElement(inputsArray[i][1]);
    }
}

function createHiddenDivForTextboxAndHideTextbox(textbox)
{
    createHiddenDivForElementAndHideElement(textbox);
}

function removeHiddenDivForTextboxAndShowTextbox(textbox)
{
    removeHiddenDivForElement(textbox);
}

function createHiddenDivForSelectAndHideSelect(select)
{
    createHiddenDivForElementAndHideElement(select);
}

function removeHiddenDivForSelectAndShowSelect(select)
{
    removeHiddenDivForElement(select);
}

function removeHiddenDivForElement(element)
{
    if(document.getElementById('there_can_only_be_one')){
        return;
    }
    var hidden_div_id = 'there_can_be_more_than_one_'+element.id;
    var hidden_div = document.querySelector('#'+hidden_div_id);
    if(hidden_div){
        hidden_div.remove();
    }
    if(element.classList.contains('hidden')){
        element.classList.remove('hidden');
    }
    if(element.classList.contains('readspeaker_hidden_element_multiple')){
        element.classList.remove('readspeaker_hidden_element_multiple');
    }
    if(element.classList.contains('readspeaker_hidden_element')){
        element.classList.remove('readspeaker_hidden_element');
    }
}

function createHiddenDivForElementAndHideElement(element)
{
    if(!element){
        return;
    }
    if(element.nodeName=='INPUT'&&element.value==''){
        return;
    }
    var hidden_div = document.createElement('div');
    element.parentNode.insertBefore(hidden_div,element);
    hidden_div.id = 'there_can_be_more_than_one_'+element.id;
    if(element.nodeName=='INPUT'){
        hidden_div.innerHTML = element.value;
        hidden_div.classList.add('rs-shadow-input');
    }
    if(element.nodeName=='SELECT'){
        hidden_div.innerHTML = element.title;
        if(element.title==''){
            hidden_div.innerHTML = element.firstChild.innerHTML;
        }
        hidden_div.classList.add('rs-shadow-select');
    }
    hidden_div.style.height = element.offsetHeight+'px';
    hidden_div.style.width = element.offsetWidth+'px';
    hidden_div.style.display = 'inline-flex';
    hidden_div.classList.add('form-input');
    hidden_div.classList.add('overflow-ellipsis');
    hidden_div.classList.add('readspeaker_readable_element');
    element.classList.add('hidden');
    element.classList.add('readspeaker_hidden_element');
}

function mouseenterSelect(event,selectId,questionId)
{
    rspkr.tlc_clicklisten_active = rspkr.ui.Tools.ClickListen.active();
    var popup = document.querySelector('.rsbtn_popup_select_'+questionId);
    if(popup == null){
        return;
    }
    if(popup.classList.contains('hidden')){
        popup.classList.remove('hidden');
    }
    var element = event.toElement;
    var rect = element.getBoundingClientRect();
    popup.style.left = rect.left+'px';
    popup.style.top = rect.top-270+'px';
    popup.linkedElement = element;
    popup.addEventListener("click", showReadableSelect, false);
    setTimeout(hideRsTlcPopup.bind(null, popup),3000);
}

function hideRsTlcPopup(popup)
{
    if(!popup.classList.contains('hidden')){
        popup.classList.add('hidden');
    }
    popup.removeEventListener('click',showReadableSelect);
}

function showReadableSelect()
{
    rspkr.rs_tlc_prevent_close = true;
    if(!rspkr.ui.Tools.ClickListen.active()){
        rspkr.ui.Tools.ClickListen.activate();
    }
    var rect = this.linkedElement.getBoundingClientRect();
    var readable_div = getReadableDivForSelect(this.linkedElement);
    this.parentNode.insertBefore(readable_div,this);
    readable_div.style.position = 'absolute';
    readable_div.style.top = rect.top-270+'px';
    readable_div.style.left = rect.left+'px';
    hideRsTlcPopup(this);
    readable_div.click();
}


function getReadableDivForSelect(select)
{
    removeOldElement();
    var readable_div = document.createElement('div');
    readable_div.id = 'there_can_only_be_one';
    var ul = document.createElement('ul');
    readable_div.appendChild(ul);
    for (var i = 0; i < select.options.length; i++) {
        var li = document.createElement('li');
        li.innerHTML = select.options[i].innerHTML;
        ul.appendChild(li);
    }
    readable_div.classList.add('rs-shadow-select-popup');
    readable_div.classList.add('overflow-ellipsis');
    readable_div.classList.add('rs-click-listen');
    return readable_div;
}

function setMobileClasses(eventType)
{
    if(!rspkr.mobile()){
        return;
    }
    var rs_button = document.querySelector('#readspeaker_button1');
    if(eventType=='pause'){
        if(rs_button==null){
            return;
        }
        if(rs_button.classList.contains('rs_tlc_paused')){
            return;
        }
        rs_button.classList.add('rs_tlc_paused');
    }
    if(eventType=='play'||eventType=='stop'){
        if(rs_button==null){
            return;
        }
        if(!rs_button.classList.contains('rs_tlc_paused')){
            return;
        }
        rs_button.classList.remove('rs_tlc_paused');
    }
}
