// ReadSpeaker.q(function() {
//     console.log('rs_tlc_skin initialized!');
//     rspkr.rs_tlc_play_started = false;
//     registerTlcClickListenActive();
//     rspkr.rs_tlc_prevent_close = false;
//     rspkr.rs_tlc_container = false;
//
// });
// window.rsConf = {
//     general: {
//         usePost: true,
//         skipHiddenContent:true
//     },
//     ui: {
//         scrollcontrols: {
//             vertical : 'top',
//             horizontal: 'left'
//         },
//         toolbar: {
//             inverted : false
//         },
//         tools: {
//             textmode : false
//         },
//         mobileVertPos: 'bottom=100'
//     },
//     cb: {
//         ui: {
//             beforeclose: function(){
//                 var focusedElements = document.getElementsByClassName('rs-cl-tabbable');
//                 if(typeof focusedElements.length == "undefined" || focusedElements.length==0){
//                     return;
//                 }
//                 focusedElements[0].classList.remove('rs-cl-tabbable');
//             },
//             close: function() {
//                 console.log('Player closed and callback fired!');
//                 // var oldEl = document.getElementById('there_can_only_be_one');
//                 // var playerStarted = (typeof rspkr.rs_tlc_play_started != "undefined")?rspkr.rs_tlc_play_started:false;
//                 // if(oldEl && playerStarted){
//                 //     oldEl.remove();
//                 //     displayHiddenElements();
//                 //     window.getSelection().removeAllRanges();
//                 // }
//                 hideRsPlayer();
//                 window.document.dispatchEvent(new Event("readspeaker_closed", {
//                     bubbles: true,
//                     cancelable: true
//                 }));
//             },
//             stop: function() {
//                 setMobileClasses('stop');
//                 console.log('Player stopped and callback fired!');
//                 if(typeof rspkr.tlc_clicklisten_active=='undefined'){
//                     rspkr.ui.getActivePlayer().close();
//                 }
//                 if(rspkr.tlc_clicklisten_active){
//                     return activateClickTap();
//                 }
//                 deactivateClickTap();
//                 rspkr.ui.getActivePlayer().close();
//             },
//             open: function() {
//                 console.log('Open callback fired!');
//                 window.document.dispatchEvent(new Event("readspeaker_opened", {
//                     bubbles: true,
//                     cancelable: true
//                 }));
//             },
//             play: function() {
//                 console.log('Play callback fired!');
//                 setMobileClasses('play');
//                 rspkr.rs_tlc_play_started = true;
//                 rspkr.rs_tlc_prevent_close = false;
//                 showRsPlayer();
//                 window.document.dispatchEvent(new Event("readspeaker_started", {
//                     bubbles: true,
//                     cancelable: true
//                 }));
//             },
//             pause: function() {
//                 console.log('Pause callback fired!');
//                 setMobileClasses('pause');
//                 rspkr.rs_tlc_play_started = false;
//             }
//         }
//     }
// };


function startRsPlayer()
{
    ReadspeakerTlc.player.startRsPlayer();
    return;
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
    console.dir('rs_tlc_skin');
    //if clickListen is activated you cannot type an L in a textfield
    registerTlcClickListenActive();
    deactivateClickTap();
}
function handleBlurForReadspeaker()
{
    console.dir('rs_tlc_skin');
    if(typeof rspkr.tlc_clicklisten_active == 'undefined'){
        return;
    }
    if(rspkr.tlc_clicklisten_active){
        rspkr.rs_tlc_play_started = false;
        activateClickTap();
    }
}
function handleTextBoxFocusForReadspeaker(focusEvent,questionId)
{
    ReadspeakerTlc.rsTlcEvents.handleTextBoxFocusForReadspeaker(focusEvent,questionId);
    return;
    handleFocusForReadspeaker();
    var correction = {x:-10,y:-247};
    var popup = getRsbtnPopupTlc(questionId,focusEvent,correction);
    if(popup == null){
        return;
    }
    var obj = focusEvent.target;
    popup.addEventListener("click", function(){readTextbox(event,obj);}, false);
}
function handleTextBoxBlurForReadspeaker(event,questionId)
{
        ReadspeakerTlc.rsTlcEvents.handleTextBoxBlurForReadspeaker(event,questionId);
        return;
        handleBlurForReadspeaker();
        rsRemovRsbtnPopupTlcForQuestion(event,questionId);
}
function handleClickListenToggle()
{
    console.dir('rs_tlc_skin');
    if(typeof rspkr.tlc_clicklisten_active == "undefined"){
        return;
    }
    if(tlcClickListenActiveIsInSync()){
        return;
    }
    if(rspkr.tlc_clicklisten_active){
        activateClickTap();
        return;
    }
    deactivateClickTap();
}


function handleMouseupForReadspeaker(e,obj)
{
    console.dir('rs_tlc_skin');
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
    activateClickTap();
    hidden_div.click();
}

function getValueOfInput(obj)
{
    console.dir('rs_tlc_skin');
    if(obj == ''  || obj == null){
        return '';
    }
    return obj.title;
}

function doNotReadInput(element)
{
    console.dir('rs_tlc_skin');
    if(element == ''  || element == null){
        return true;
    }
    try {
        if (element.matches(':-internal-autofill-selected') && element.title != '') {
            return false;
        }
    }catch(error){
        //silentfail
    }
    try {
        if(element.matches(':-webkit-autofill')&&element.title !=''){
            return false;
        }
    }catch(error){
        //silentfail
    }
    try {
        if(element.matches(':autofill')&&element.title !=''){
            return false;
        }
    }catch(error){
        //silentfail
    }
    if(element.title ==''&&element.value ==''){
        return true;
    }

    return false;
}

function readCkEditorOnSelect(editor)
{
    console.dir('rs_tlc_skin');
    if(typeof rspkr == "undefined"){
        return;
    }
    rspkr.rs_tlc_play_started = false;
    removeOldElement();
    var node = cloneHiddenSpan(editor);
    setSelectedElement(node,editor);
    removeSelectionFromEditor(editor);
    document.getElementsByClassName('cke_wysiwyg_frame')[0].classList.add('hidden');
    document.getElementsByClassName('cke_wysiwyg_frame')[0].classList.add('readspeaker_hidden_element');
    document.getElementsByClassName('rs-click-listen')[0].click();
}

function removeSelectionFromEditor(editor) {
    console.dir('rs_tlc_skin');
    var range = new CKEDITOR.dom.range(editor.document);
    var body = editor.document.getBody();
    range.setStart(body, 0);
    range.setEnd(body, 0);
    editor.getSelection().selectRanges([range]);
}

function removeOldElement()
{
    console.dir('rs_tlc_skin');
    var oldEl = document.getElementById('there_can_only_be_one');
    if(oldEl){
        oldEl.remove();
    }
}
function removeReadableElements()
{
    console.dir('rs_tlc_skin');
    var elements = document.getElementsByClassName('readspeaker_readable_element');
    for (var i=0; i < elements.length; i++) {
        elements[i].remove();
    }
}

function cloneHiddenSpan(editor)
{
    console.dir('rs_tlc_skin');
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
    console.dir('rs_tlc_skin');
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
    console.dir('rs_tlc_skin');
    if(rspkr.rs_tlc_play_started){
        return;
    }
    activateClickTap();
    rspkr.rs_tlc_play_started = false;
    rspkr.rs_tlc_prevent_close = true;
    var hidden_div = createHiddenDivTextArea(questionId)
    hidden_div.click();
}

function createHiddenDivTextArea(questionId){
    console.dir('rs_tlc_skin');
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
    console.dir('rs_tlc_skin');
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
    console.dir('rs_tlc_skin');
    hideByClassName('rs_starter_button');
    showById('readspeaker_button1');
}

function hideRsPlayer()
{
    console.dir('rs_tlc_skin');
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
    console.dir('rs_tlc_skin');
    removeOldElement();
    removeReadableElementsAndDisplayHiddenElements();
}
function removeReadableElementsAndDisplayHiddenElements()
{
    console.dir('rs_tlc_skin');
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
    console.dir('rs_tlc_skin');
    var element = document.getElementById(id);
    if(element){
        element.classList.remove('hidden');
    }
}

function hideById(id)
{
    console.dir('rs_tlc_skin');
    var element = document.getElementById(id);
    if(element){
        element.classList.add('hidden');
    }
}

function showByClassName(class_name)
{
    console.dir('rs_tlc_skin');
    var elements = document.getElementsByClassName(class_name);
    if(elements){
        [].forEach.call(elements, function (el) {
            el.classList.remove('hidden');
        });
    }
}

function hideByClassName(class_name)
{
    console.dir('rs_tlc_skin');
    var elements = document.getElementsByClassName(class_name);
    if(elements){
        [].forEach.call(elements, function (el) {
            el.classList.add('hidden');
        });
    }
}

function disableContextMenuOnCkeditor()
{
    ReadspeakerTlc.ckeditor.disableContextMenuOnCkeditor();
    return;
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
    return ReadspeakerTlc.guard.shouldNotReinitCkeditor(el);
    if(!checkElementInActiveQuestion(el)){
        return true;
    }
    return false;
}

function shouldNotCreateHiddenTextarea(id)
{
    return ReadspeakerTlc.guard.shouldNotCreateHiddenTextarea(id);
    return ;
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
    console.dir('rs_tlc_skin');
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
    console.dir('rs_tlc_skin');
    if(possibleTextarea.id==id) {
        return true;
    }
    return false;
}
function checkElementInActiveQuestion(el)
{
    return ReadspeakerTlc.util.checkElementInActiveQuestion();
    var container = el.closest('.rs_readable');
    if(container){
        return true;
    }
    return false;
}

function shouldNotCreateHiddenDivsForTextboxesCompletion(containerId)
{
    return ReadspeakerTlc.guard.shouldNotCreateHiddenDivsForTextboxesCompletion(containerId);
    return shouldNotCreateHiddenDivs(containerId);
}

function createHiddenDivsForTextboxesCompletion(containerId)
{
    console.dir('rs_tlc_skin');
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
    return ReadspeakerTlc.guard.shouldNotCreateHiddenDivsForSelects(containerId);
    return shouldNotCreateHiddenDivs(containerId);
}

function shouldNotCreateHiddenDivs(containerId)
{
    console.dir('rs_tlc_skin');
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
    ReadspeakerTlc.hiddenElement.createHiddenDivsForSelects(containerId);
    return;
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
    console.dir('rs_tlc_skin');
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
    console.dir('rs_tlc_skin');
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
    console.dir('rs_tlc_skin');
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
    console.dir('rs_tlc_skin');
    createHiddenDivForElementAndHideElement(textbox);
}

function removeHiddenDivForTextboxAndShowTextbox(textbox)
{
    console.dir('rs_tlc_skin');
    removeHiddenDivForElement(textbox);
}

function createHiddenDivForSelectAndHideSelect(select)
{
    console.dir('rs_tlc_skin');
    createHiddenDivForElementAndHideElement(select);
}

function removeHiddenDivForSelectAndShowSelect(select)
{
    console.dir('rs_tlc_skin');
    removeHiddenDivForElement(select);
}

function removeHiddenDivForElement(element)
{
    console.dir('rs_tlc_skin');
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
    console.dir('rs_tlc_skin');
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

function rsFocusSelect(event,selectId,questionId)
{
    ReadspeakerTlc.rsTlcEvents.rsFocusSelect(event,selectId,questionId);
    return;
    registerTlcClickListenActive();
    // var popup = document.querySelector('.rsbtn_popup_tlc_'+questionId);
    // if(popup == null){
    //     return;
    // }
    // if(popup.classList.contains('hidden')){
    //     popup.classList.remove('hidden');
    // }
    // var element = event.currentTarget;
    // var rect = element.getBoundingClientRect();
    // popup.style.left = rect.left+35+'px';
    // popup.style.top = rect.top-230+'px';
    // popup.linkedElement = element;
    var correction = {x:17,y:-247};
    var popup = getRsbtnPopupTlc(questionId,event,correction);
    if(popup == null){
        return;
    }
    popup.addEventListener("click", showReadableSelect, false);
}

function getRsbtnPopupTlc(questionId,event,correction)
{
    console.dir('rs_tlc_skin');
    var popup = document.querySelector('.rsbtn_popup_tlc_'+questionId);
    if(popup == null){
        return popup;
    }
    if(popup.classList.contains('hidden')){
        popup.classList.remove('hidden');
    }
    var element = event.currentTarget;
    var rect = element.getBoundingClientRect();
    popup.style.left = rect.left+correction.x+'px';
    popup.style.top = rect.top+correction.y+'px';
    popup.linkedElement = element;
    return popup;
}

function rsBlurSelect(event,questionId)
{
    ReadspeakerTlc.rsTlcEvents.rsBlurSelect(event,questionId);
    return;
    rsRemovRsbtnPopupTlcForQuestion(event,questionId);
}

function rsRemovRsbtnPopupTlcForQuestion(event,questionId)
{
    console.dir('rs_tlc_skin');
    var popup = document.querySelector('.rsbtn_popup_tlc_'+questionId);
    if(popup == null){
        return;
    }
    if(event.target!=popup.linkedElement){
        return;
    }
    setTimeout(hideRsTlcPopup.bind(null, popup,event),500);
}


function hideRsTlcPopup(popup,event)
{
    console.dir('rs_tlc_skin');
    if(event.target!=popup.linkedElement){
        return;
    }
    if(!popup.classList.contains('hidden')){
        popup.classList.add('hidden');
    }
    popup.removeEventListener('click',showReadableSelect);
}

function showReadableSelect(event)
{
    console.dir('rs_tlc_skin');
    rspkr.rs_tlc_prevent_close = true;
    activateClickTap();
    var rect = this.linkedElement.getBoundingClientRect();
    var readable_div = getReadableDivForSelect(this.linkedElement);
    this.parentNode.insertBefore(readable_div,this);
    readable_div.style.position = 'absolute';
    readable_div.style.top = rect.top-230+'px';
    readable_div.style.left = rect.left+35+'px';
    hideRsTlcPopup(this,event);
    readable_div.click();
}


function getReadableDivForSelect(select)
{
    console.dir('rs_tlc_skin');
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

function readTextbox(event,obj)
{
    console.dir('rs_tlc_skin');
    removeOldElement();
    rspkr.rs_tlc_play_started = false;
    if(doNotReadInput(obj)){
        return;
    }
    var hidden_div = document.createElement('div');
    obj.parentNode.insertBefore(hidden_div,obj);
    hidden_div.id = 'there_can_only_be_one';
    hidden_div.innerHTML = getValueOfInput(obj);
    hidden_div.style.height = obj.offsetHeight+'px';
    hidden_div.style.width = obj.offsetWidth+'px';
    hidden_div.style.display = 'inline-flex';
    hidden_div.classList.add('rs-click-listen');
    hidden_div.classList.add('rs-shadow-input');
    hidden_div.classList.add('form-input');
    hidden_div.classList.add('overflow-ellipsis');
    obj.classList.add('hidden');
    obj.classList.add('readspeaker_hidden_element');
    var container = obj.closest('.completion-question-container');
    if(container){
        rspkr.rs_tlc_container = container;
    }
    activateClickTap();
    hidden_div.click();
}

function activateClickTap()
{
    console.dir('rs_tlc_skin');
    if(rspkr.mobile()){
        return activateMouseTracker();
    }
    activateClickListen();
}
function activateClickListen()
{
    console.dir('rs_tlc_skin');
    if(!rspkr.ui.Tools.ClickListen.active()){
        rspkr.ui.Tools.ClickListen.activate();
    }
}
function activateMouseTracker()
{
    console.dir('rs_tlc_skin');
    if(!rspkr.ui.Tools.MouseTracker.isActive()){
        window.ReadSpeaker.Mobile.ui.Player().tapRead.activate();
        //document.querySelector('.rsmpl-tapread>button').click();
        //rspkr.ui.Tools.MouseTracker.activate();
    }
}
function deactivateClickTap()
{
    console.dir('rs_tlc_skin');
    if(rspkr.mobile()){
        return deactivateMouseTracker();
    }
    deactivateClickListen();
}
function deactivateClickListen()
{
    console.dir('rs_tlc_skin');
    if(rspkr.ui.Tools.ClickListen.active()){
        rspkr.ui.Tools.ClickListen.deactivate();
    }
}
function deactivateMouseTracker()
{
    console.dir('rs_tlc_skin');
    if(rspkr.ui.Tools.MouseTracker.isActive()){
        window.ReadSpeaker.Mobile.ui.Player().tapRead.activate();
        //document.querySelector('.rsmpl-tapread>button').click();
        //rspkr.ui.Tools.MouseTracker.inactivate();
    }
}
function registerTlcClickListenActive()
{
    ReadspeakerTlc.register.registerTlcClickListenActive();
    return;
    if(rspkr.mobile()){
        rspkr.tlc_clicklisten_active = rspkr.ui.Tools.MouseTracker.isActive();
    }else{
        rspkr.tlc_clicklisten_active = rspkr.ui.Tools.ClickListen.active();
    }
}
function tlcClickListenActiveIsInSync()
{
    console.dir('rs_tlc_skin');
    if(rspkr.mobile()){
        return (rspkr.tlc_clicklisten_active == rspkr.ui.Tools.MouseTracker.isActive());
    }else{
        return (rspkr.tlc_clicklisten_active == rspkr.ui.Tools.ClickListen.active());
    }
}
function setMobileClasses(eventType)
{
    console.dir('rs_tlc_skin');
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

