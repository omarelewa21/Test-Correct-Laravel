ReadspeakerTlc = function(){
    rsTlcEvents = function(){
        function handleTextBoxFocusForReadspeaker(focusEvent,questionId)
        {
            handleFocusForReadspeaker();
            var correction = {x:-10,y:-247};
            var p = popup.getRsbtnPopupTlc(questionId,focusEvent,correction);
            if(p == null){
                return;
            }
            var obj = focusEvent.target;
            p.addEventListener("click", read.readTextbox, { once: true });
        }
        function handleTextBoxBlurForReadspeaker(event,questionId)
        {
            handleBlurForReadspeaker();
            popup.rsRemovRsbtnPopupTlcForQuestion(event,questionId);
        }
        function handleFocusForReadspeaker()
        {
            //if clickListen is activated you cannot type an L in a textfield
            register.registerTlcClickListenActive();
            clickListen.deactivateClickTap();
        }
        function handleTextareaFocusForReadspeaker(focusEvent,questionId)
        {
            handleFocusForReadspeaker();
            var correction = {x:-15,y:6};
            var p = popup.getRsbtnPopupTlc(questionId,focusEvent,correction);
            if(p == null){
                return;
            }
            var obj = focusEvent.target;
            p.addEventListener("click", read.readTextArea, { once: true });
        }
        function handleTextareaBlurForReadspeaker()
        {
            handleBlurForReadspeaker();
        }
        function handleCkeditorFocusForReadspeaker(ckeditorNode,questionId)
        {
            handleFocusForReadspeaker();
            var correction = {x:-15,y:6};
            var p = popup.getRsbtnPopupTlcElement(questionId,ckeditorNode,correction);
            if(p == null){
                return;
            }
            p.addEventListener("click", read.readTextArea, { once: true });
        }
        function handleCkeditorBlurForReadspeaker(ckeditorNode)
        {

        }
        function handleBlurForReadspeaker()
        {
            if(typeof rspkr.tlc_clicklisten_active == 'undefined'){
                return;
            }
            if(rspkr.tlc_clicklisten_active){
                rspkr.rs_tlc_play_started = false;
                clickListen.activateClickTap();
            }
        }
        function rsFocusSelect(event,selectId,questionId)
        {
            register.registerTlcClickListenActive();
            var correction = {x:17,y:-247};
            var p = popup.getRsbtnPopupTlc(questionId,event,correction);
            if(p == null){
                return;
            }
            p.addEventListener("click", util.showReadableSelect, false);
        }
        function rsBlurSelect(event,questionId)
        {
            popup.rsRemovRsbtnPopupTlcForQuestion(event,questionId);
        }


        return{
            handleTextBoxFocusForReadspeaker:handleTextBoxFocusForReadspeaker,
            handleTextBoxBlurForReadspeaker:handleTextBoxBlurForReadspeaker,
            rsFocusSelect:rsFocusSelect,
            rsBlurSelect:rsBlurSelect,
            handleTextareaFocusForReadspeaker:handleTextareaFocusForReadspeaker,
            handleTextareaBlurForReadspeaker:handleTextareaBlurForReadspeaker,
            handleCkeditorFocusForReadspeaker:handleCkeditorFocusForReadspeaker,
            handleCkeditorBlurForReadspeaker:handleCkeditorBlurForReadspeaker
        }
    }();
    clickListen = function(){
        function activateClickTap()
        {
            if(rspkr.mobile()){
                return activateMouseTracker();
            }
            activateClickListen();
        }
        function activateClickListen()
        {
            if(!rspkr.ui.Tools.ClickListen.active()){
                rspkr.ui.Tools.ClickListen.activate();
            }
        }
        function activateMouseTracker()
        {
            if(!rspkr.ui.Tools.MouseTracker.isActive()){
                window.ReadSpeaker.Mobile.ui.Player().tapRead.activate();
                //document.querySelector('.rsmpl-tapread>button').click();
                //rspkr.ui.Tools.MouseTracker.activate();
            }
        }
        function deactivateClickTap()
        {
            if(rspkr.mobile()){
                return deactivateMouseTracker();
            }
            deactivateClickListen();
        }
        function deactivateClickListen()
        {
            if(rspkr.ui.Tools.ClickListen.active()){
                rspkr.ui.Tools.ClickListen.deactivate();
            }
        }
        function deactivateMouseTracker()
        {
            if(rspkr.ui.Tools.MouseTracker.isActive()){
                window.ReadSpeaker.Mobile.ui.Player().tapRead.activate();
                //document.querySelector('.rsmpl-tapread>button').click();
                //rspkr.ui.Tools.MouseTracker.inactivate();
            }
        }

        function tlcClickListenActiveIsInSync()
        {
            if(rspkr.mobile()){
                return (rspkr.tlc_clicklisten_active == rspkr.ui.Tools.MouseTracker.isActive());
            }else{
                return (rspkr.tlc_clicklisten_active == rspkr.ui.Tools.ClickListen.active());
            }
        }
        return{
            activateClickTap:activateClickTap,
            deactivateClickTap:deactivateClickTap
        }
    }();
    hiddenElement = function(){
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
        function createHiddenDivTextArea(textarea){

            var hidden_div = getHiddenDivForTextarea(textarea);
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

        function getHiddenDivForTextarea(textarea)
        {
            var oldEl = document.getElementById('there_can_only_be_one');
            var possibleTextarea = false;
            var hidden_div;
            if(oldEl){
                possibleTextarea = oldEl.nextElementSibling;
            }
            if(possibleTextarea.id==textarea.id){
                hidden_div = oldEl;
            }else{
                removeOldElement();
                hidden_div = document.createElement('div');
            }
            return hidden_div;
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
        function createHiddenDivForSelectAndHideSelect(select)
        {
            createHiddenDivForElementAndHideElement(select);
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
        return{
            removeOldElement:removeOldElement,
            removeReadableElements:removeReadableElements,
            cloneHiddenSpan:cloneHiddenSpan,
            displayHiddenElementsAndRemoveTheRest:displayHiddenElementsAndRemoveTheRest,
            getReadableDivForSelect:getReadableDivForSelect,
            createHiddenDivsForSelects:createHiddenDivsForSelects,
            createHiddenDivTextArea:createHiddenDivTextArea
        }
    }();
    register = function(){
        function registerTlcClickListenActive()
        {
            if(rspkr.mobile()){
                rspkr.tlc_clicklisten_active = rspkr.ui.Tools.MouseTracker.isActive();
            }else{
                rspkr.tlc_clicklisten_active = rspkr.ui.Tools.ClickListen.active();
            }
        }
        return{
            registerTlcClickListenActive:registerTlcClickListenActive
        }
    }()
    read = function(){
        function readTextbox(event)
        {
            popup.hideRsTlcPopup(this);
            var obj = this.linkedElement;
            hiddenElement.removeOldElement();
            rspkr.rs_tlc_play_started = false;
            rspkr.rs_tlc_prevent_close = true;
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
            clickListen.activateClickTap();
            hidden_div.click();
        }
        function readTextArea(event)
        {
            popup.hideRsTlcPopup(this);
            if(rspkr.rs_tlc_play_started){
                return;
            }
            clickListen.activateClickTap();
            rspkr.rs_tlc_play_started = false;
            rspkr.rs_tlc_prevent_close = true;
            var textarea = this.linkedElement;
            var hidden_div = hiddenElement.createHiddenDivTextArea(textarea);
            hidden_div.click();
        }
        function readSelect(event)
        {
            rspkr.rs_tlc_prevent_close = true;
            clickListen.activateClickTap();
            var rect = this.linkedElement.getBoundingClientRect();
            var readable_div = hiddenElement.getReadableDivForSelect(this.linkedElement);
            this.parentNode.insertBefore(readable_div,this);
            readable_div.style.position = 'absolute';
            readable_div.style.top = rect.top-230+'px';
            readable_div.style.left = rect.left+35+'px';
            popup.hideRsTlcPopup(this);
            readable_div.click();
        }
        function doNotReadInput(element)
        {
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
        function getValueOfInput(obj)
        {
            if(obj == ''  || obj == null){
                return '';
            }
            return obj.title;
        }
        return {
            readTextbox: readTextbox,
            doNotReadInput:doNotReadInput,
            readTextArea: readTextArea
        }
    }();
    popup = function(){
        function getRsbtnPopupTlc(questionId,event,correction)
        {
            var element = event.currentTarget;
            return getRsbtnPopupTlcElement(questionId,element,correction);
            // var p = document.querySelector('.rsbtn_popup_tlc_'+questionId);
            // if(p == null){
            //     return p;
            // }
            // if(p.classList.contains('hidden')){
            //     p.classList.remove('hidden');
            // }
            // var element = event.currentTarget;
            // var rect = element.getBoundingClientRect();
            // switch (element.nodeName){
            //     case 'INPUT':
            //     case 'SELECT':
            //         p.style.left = rect.left+correction.x+'px';
            //         p.style.top = rect.top+correction.y+'px';
            //         break;
            //     case 'TEXTAREA':
            //         p.style.left = rect.width+correction.x+'px';
            //         p.style.top = correction.y+'px';
            // }
            // p.linkedElement = element;
            // return p;
        }
        function getRsbtnPopupTlcElement(questionId,element,correction)
        {
            var p = document.querySelector('.rsbtn_popup_tlc_'+questionId);
            if(p == null){
                return p;
            }
            if(p.classList.contains('hidden')){
                p.classList.remove('hidden');
            }
            var rect = element.getBoundingClientRect();
            switch (element.nodeName){
                case 'INPUT':
                case 'SELECT':
                    p.style.left = rect.left+correction.x+'px';
                    p.style.top = rect.top+correction.y+'px';
                    break;
                case 'TEXTAREA':
                    p.style.left = rect.width+correction.x+'px';
                    p.style.top = correction.y+'px';
            }
            p.linkedElement = element;
            return p;
        }
        function rsRemovRsbtnPopupTlcForQuestion(event,questionId)
        {
            var p = document.querySelector('.rsbtn_popup_tlc_'+questionId);
            if(p == null){
                return;
            }
            if(event.target!=p.linkedElement){
                return;
            }
            setTimeout(hideRsTlcPopupWithEvent.bind(null, p,event),500);
        }

        function hideRsTlcPopupWithEvent(p,event)
        {
            if(event.target!=p.linkedElement){
                return;
            }
            hideRsTlcPopup(p);
        }

        function hideRsTlcPopup(p)
        {

            if(!p.classList.contains('hidden')){
                p.classList.add('hidden');
            }
            switch (p.linkedElement.nodeName){
                case 'TEXTAREA':
                    p.removeEventListener('click',read.TextArea);
                    break;
                case 'INPUT':
                    p.removeEventListener('click',read.TextBox);
                    break;
                case 'SELECT':
                    p.removeEventListener('click',read.readSelect);
                    break;
                default:
                    p.removeEventListener('click');
            }

        }
        return{
            getRsbtnPopupTlc:getRsbtnPopupTlc,
            rsRemovRsbtnPopupTlcForQuestion:rsRemovRsbtnPopupTlcForQuestion,
            hideRsTlcPopup:hideRsTlcPopup,
            getRsbtnPopupTlcElement:getRsbtnPopupTlcElement
        }
    }();
    player = function(){
        function hideRsPlayer()
        {
            if(rspkr.rs_tlc_prevent_close){
                return;
            }
            util.showByClassName('rs_starter_button');
            util.hideById('readspeaker_button1');
            rspkr.rs_tlc_play_started = false;
            hiddenElement.displayHiddenElementsAndRemoveTheRest();
            window.getSelection().removeAllRanges();
        }
        function showRsPlayer()
        {
            util.hideByClassName('rs_starter_button');
            util.showById('readspeaker_button1');
        }
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
        return{
            hideRsPlayer:hideRsPlayer,
            showRsPlayer:showRsPlayer,
            startRsPlayer:startRsPlayer
        }
    }();
    util = function(){
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
        function showReadableSelect(event)
        {
            rspkr.rs_tlc_prevent_close = true;
            clickListen.activateClickTap();
            var rect = this.linkedElement.getBoundingClientRect();
            var readable_div = hiddenElement.getReadableDivForSelect(this.linkedElement);
            this.parentNode.insertBefore(readable_div,this);
            readable_div.style.position = 'absolute';
            readable_div.style.top = rect.top-230+'px';
            readable_div.style.left = rect.left+35+'px';
            popup.hideRsTlcPopup(this);
            readable_div.click();
        }
        return{
            showById:showById,
            hideById:hideById,
            showByClassName:showByClassName,
            hideByClassName:hideByClassName,
            checkPossibleTextAreaValid:checkPossibleTextAreaValid,
            checkPossibleTextAreaAlreadyExists:checkPossibleTextAreaAlreadyExists,
            checkElementInActiveQuestion:checkElementInActiveQuestion,
            showReadableSelect:showReadableSelect
        }
    }();
    ckeditor = function(){
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
            if(!util.checkElementInActiveQuestion(el)){
                return true;
            }
            return false;
        }
        return{
            disableContextMenuOnCkeditor:disableContextMenuOnCkeditor,
            shouldNotReinitCkeditor:shouldNotReinitCkeditor
        }
    }();
    guard = function(){
        function shouldNotCreateHiddenTextarea(id)
        {
            var oldEl = document.getElementById('there_can_only_be_one');
            var possibleTextarea = false;
            var textareaId = 'textarea_'+id;
            var currentTextarea = document.querySelector('#'+textareaId);
            if(currentTextarea && !util.checkElementInActiveQuestion(currentTextarea)){
                return true;
            }
            if(oldEl){
                possibleTextarea = oldEl.nextElementSibling;
            }
            if(possibleTextarea && !util.checkElementInActiveQuestion(oldEl)){
                return true;
            }
            if(possibleTextarea && !util.checkPossibleTextAreaValid(possibleTextarea)){
                return true;
            }
            if(possibleTextarea && util.checkPossibleTextAreaAlreadyExists(possibleTextarea,textareaId)){
                return true;
            }
            return false;
        }
        function shouldNotCreateHiddenDivsForSelects(containerId)
        {
            return shouldNotCreateHiddenDivs(containerId);
        }
        function shouldNotCreateHiddenDivsForTextboxesCompletion(containerId)
        {
            return shouldNotCreateHiddenDivs(containerId);
        }
        function shouldNotReinitCkeditor(el)
        {
            if(!util.checkElementInActiveQuestion(el)){
                return true;
            }
            return false;
        }
        function shouldNotCreateHiddenDivs(containerId)
        {
            if(document.getElementById('there_can_only_be_one')){
                return true;
            }
            var element = document.querySelector('#'+containerId);
            if(element && !util.checkElementInActiveQuestion(element)){
                return true;
            }
            return false;
        }
        return{
            shouldNotCreateHiddenTextarea:shouldNotCreateHiddenTextarea,
            shouldNotCreateHiddenDivsForSelects:shouldNotCreateHiddenDivsForSelects,
            shouldNotCreateHiddenDivsForTextboxesCompletion:shouldNotCreateHiddenDivsForTextboxesCompletion,
            shouldNotReinitCkeditor:shouldNotReinitCkeditor
        }
    }();
    return{
        rsTlcEvents:rsTlcEvents,
        player:player,
        guard:guard,
        clickListen:clickListen,
        ckeditor:ckeditor,
        hiddenElement:hiddenElement,
        register:register
    }
}();
ReadSpeaker.q(function() {
    console.log('rs_tlc_skin initialized!');
    rspkr.rs_tlc_play_started = false;
    ReadspeakerTlc.register.registerTlcClickListenActive();
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
        tools: {
            textmode : false
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
                ReadspeakerTlc.player.hideRsPlayer();
                window.document.dispatchEvent(new Event("readspeaker_closed", {
                    bubbles: true,
                    cancelable: true
                }));
            },
            stop: function() {
                console.log('Player stopped and callback fired!');
                if(typeof rspkr.tlc_clicklisten_active=='undefined'){
                    rspkr.ui.getActivePlayer().close();
                }
                if(rspkr.tlc_clicklisten_active){
                    return ReadspeakerTlc.clickListen.activateClickTap();
                }
                ReadspeakerTlc.clickListen.deactivateClickTap();
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
                rspkr.rs_tlc_play_started = true;
                rspkr.rs_tlc_prevent_close = false;
                ReadspeakerTlc.player.showRsPlayer();
                window.document.dispatchEvent(new Event("readspeaker_started", {
                    bubbles: true,
                    cancelable: true
                }));
            },
            pause: function() {
                console.log('Pause callback fired!');
                rspkr.rs_tlc_play_started = false;
            }
        }
    }
};