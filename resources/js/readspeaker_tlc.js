ReadspeakerTlc = function(){
    rsTlcEvents = function(){
        function handleTextBoxFocusForReadspeaker(focusEvent,questionId)
        {
            handleFocusForReadspeaker();
            var correction = {x:-16,y:-16};
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
        function handleTextareaBlurForReadspeaker(event,questionId)
        {
            popup.rsRemovRsbtnPopupTlcForQuestion(event,questionId);
            handleBlurForReadspeaker();
        }
        function handleCkeditorFocusForReadspeaker(ckeditorNode,questionId,editorId)
        {
            if(popup.alreadyThere(questionId)){
                return;
            }
            if(rspkr.rs_tlc_prevent_ckeditor_focus){
                return;
            }

            handleFocusForReadspeaker();
            ckeditorNode.editorId = editorId;
            var correction = {x:-15,y:2};
            var p = popup.getRsbtnPopupTlcElement(questionId,ckeditorNode,correction);
            if(p == null){
                return;
            }
            p.addEventListener("click", read.readCkeditor, { once: true });
        }
        function handleCkeditorBlurForReadspeaker(ckeditorNode,questionId,editorId)
        {
            popup.rsRemovRsbtnPopupTlcForElement(ckeditorNode,questionId);
            handleBlurForReadspeaker();
        }
        function handleCkeditorTableCellFocusForReadspeaker(element)
        {
            var config = { attributes: true, childList: false, subtree: false };
            var callback = function(mutationsList, observer){
                for(var mutation of mutationsList) {
                    if (mutation.type === 'attributes'&&mutation.attributeName=='class'&&mutation.target.classList.contains('ck-editor__nested-editable_focused')&&!mutation.target.isContentEditable ) {
                        mutation.target.setAttribute('contenteditable',true);
                    }else if(mutation.type === 'attributes'&&mutation.attributeName=='class'&&mutation.target.classList.contains('ck-editor__nested-editable_focused')){
                        delayContenteditableTrue(mutation);
                    }
                }
            }
            var observer = new MutationObserver(callback);
            observer.observe(element, config);
        }
        function delayContenteditableTrue(mutation)
        {
            var timeout = setTimeout(function(){
                if(mutation.target.isContentEditable){
                    return true;
                }
                mutation.target.setAttribute('contenteditable',true);
            },50);
        }
        function handleBlurForReadspeaker()
        {
            // if(typeof rspkr.tlc_clicklisten_active == 'undefined'){
            //     return;
            // }
            // if(rspkr.tlc_clicklisten_active){
            //     rspkr.rs_tlc_play_started = false;
            //     clickListen.activateClickTap();
            // }
        }
        function rsFocusSelect(event,selectId,questionId)
        {
            register.registerTlcClickListenActive();
            var correction = {x:-16,y:-16};
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
        function handleCkeditorSelectionChangeDoneForReadspeaker(editor)
        {
            editor.editing.view.document.on( 'selectionChangeDone', () => {
                var range = editor.model.document.selection.getFirstRange();
                if(range.end.isEqual(range.start)) {
                    clearTimeout(RichTextEditor.timer);
                    rspkr.rs_tlc_ckeditor_selecting = false;
                    editor.isReadOnly = false;
                    return;
                }
                var element = editor.ui.view.editable.element;
                rspkr.rs_tlc_ckeditor_selecting = false;
                element.addEventListener('click',ckeditorClickEvent);
            });
        }
        function handleCkeditorSelectionChangeForReadspeaker(editor)
        {

            editor.editing.view.document.on( 'selectionChange', () => {
                rspkr.rs_tlc_ckeditor_selecting = true;
                RichTextEditor.timer = setTimeout(RichTextEditor.setReadOnly.bind(null, editor),50);
            } );
        }
        function ckeditorClickEvent(event)
        {
            var editor = this.ckeditorInstance;
            if(editor === null){
                return;
            }
            if(editor.isReadOnly){
                editor.isReadOnly = false;
            }
        }
        function handleIPadSelectionChange()
        {
            if(!util.isIpadOS()){
                return;
            }
            document.addEventListener("touchend", function (event) {
                setTimeout(callRsPopup(event,"touchend"),100);
            });
            if(util.isIOS12()) {
                console.dir('ios12');
                document.addEventListener('selectionchange',userSelectionChanged);
                document.addEventListener('selectionEnd', function (event) {
                    var selectionEndTimeout = null;
                    setTimeout(callRsPopup(event,"touchend"),100);
                });
            }

        }
        function userSelectionChanged() {

            // wait 500 ms after the last selection change event
            if (selectionEndTimeout) {
                clearTimeout(selectionEndTimeout);
            }

            var selectionEndTimeout = setTimeout(function () {
                var event = new Event('selectionEnd');
                document.dispatchEvent(event);
            }, 500);
        }

        function callRsPopup(event,eventType)
        {
            var selectedText = window.getSelection().toString();
            if (selectedText == '') {
                return;
            }
            rspkr.ui.setPointerPos(event);
            rspkr.c.data.setSelectedText(event);
            rspkr.popup.lastSelectedText = selectedText;
            var rsEvent = new rspkr.lib.Facade.RSEvent();
            rsEvent.clientX = event.layerX;
            rsEvent.clientY = event.layerY;
            rsEvent.keyCode = undefined;
            rsEvent.originalEvent = event;
            rsEvent.pageX = event.pageX;
            rsEvent.pageY = event.pageY;
            rsEvent.screenX = event.layerX;
            rsEvent.screenY = event.layerY;
            rsEvent.target = document;
            rsEvent.targetTouches = undefined;
            rsEvent.type = eventType;
            rspkr.rs_popup_modified = false;
            rspkr.popup.showPopup(rsEvent);
        }
        function handlePopupChange()
        {
            var config = { attributes: true, childList: false, subtree: false };
            var element = document.querySelector('#rsbtn_popup');
            var callback = function(mutationsList, observer){
                for(var mutation of mutationsList) {
                    if (mutation.type === 'attributes'&&mutation.attributeName=='style') {
                        for (const editorId in ClassicEditors) {
                            try {
                                if (!util.checkElementInActiveQuestion(ClassicEditors[editorId].ui.view.editable.element)) {
                                    return true;
                                }
                                if(ClassicEditors[editorId].ui.view.editable.element.classList.contains('ck-read-only')){
                                    return true;
                                }
                                var range = ClassicEditors[editorId].model.document.selection.getFirstRange();
                                if(range.end.isEqual(range.start)) {
                                    return true;
                                }
                                ClassicEditors[editorId].isReadOnly = false;
                                if(ClassicEditors[editorId].ui.view.editable.element.classList.contains('ck-blurred')){
                                    ClassicEditors[editorId].ui.view.editable.element.focus();
                                }
                            } catch (error) {
                                console.dir(error);
                                return true;
                            }
                        }
                    }
                }
            }
            var observer = new MutationObserver(callback);
            observer.observe(element, config);
            var callback2 = function(mutationsList2, observer){
                if(rspkr.rs_popup_modified){
                    return true;
                }
                for(var mutation of mutationsList2) {
                    if (mutation.type === 'attributes'&&mutation.attributeName=='style'&&util.isIpadOS()) {
                        popup.positionBySelection();
                    }
                }
            }
            var observer2 = new MutationObserver(callback2);
            observer2.observe(element, config);
        }
        function addListenersToPopup()
        {
            if(document.querySelector('#rsbtn_popup')){
                ReadspeakerTlc.rsTlcEvents.handlePopupChange();
            }
            var config = { attributes: true, childList: true, subtree: false };
            var element = document.querySelector('body');
            var callback = function(mutationsList, observer){
                for(var mutation of mutationsList) {
                    if (mutation.type != 'childList') {
                        continue;
                    }
                    if(typeof mutation.addedNodes=='undefined'){
                        continue;
                    }
                    if (typeof mutation.addedNodes[0]=='undefined'){
                        continue;
                    }
                    if(mutation.addedNodes[0].id!='rsbtn_popup'){
                        continue;
                    }
                    ReadspeakerTlc.rsTlcEvents.handlePopupChange();
                    if(rspkr.rs_popup_modified||!util.isIpadOS()){
                        continue;
                    }
                    popup.positionBySelection();
                }
            }
            var observer = new MutationObserver(callback);
            observer.observe(element, config);
        }
        function addListenerCkeditorTableCellFocusForReadspeaker(element)
        {
            var iterator = document.evaluate('.//td[contains(@class, \'ck-editor__nested-editable\')]', element, null, XPathResult.ANY_TYPE, null );
            try {
                var thisNode = iterator.iterateNext();
                while (thisNode) {
                    handleCkeditorTableCellFocusForReadspeaker(thisNode);
                    thisNode = iterator.iterateNext();
                }
            }
            catch (e) {
                console.dir( 'Error: Document tree modified during iteration ' + e );
            }
        }
        return{
            handleTextBoxFocusForReadspeaker:handleTextBoxFocusForReadspeaker,
            handleTextBoxBlurForReadspeaker:handleTextBoxBlurForReadspeaker,
            rsFocusSelect:rsFocusSelect,
            rsBlurSelect:rsBlurSelect,
            handleTextareaFocusForReadspeaker:handleTextareaFocusForReadspeaker,
            handleTextareaBlurForReadspeaker:handleTextareaBlurForReadspeaker,
            handleCkeditorFocusForReadspeaker:handleCkeditorFocusForReadspeaker,
            handleCkeditorBlurForReadspeaker:handleCkeditorBlurForReadspeaker,
            handleCkeditorSelectionChangeDoneForReadspeaker:handleCkeditorSelectionChangeDoneForReadspeaker,
            handleCkeditorSelectionChangeForReadspeaker:handleCkeditorSelectionChangeForReadspeaker,
            handleIPadSelectionChange,
            handlePopupChange,
            addListenersToPopup,
            handleCkeditorTableCellFocusForReadspeaker,
            addListenerCkeditorTableCellFocusForReadspeaker,
            fixAriaLabelsForCkeditor:fixAriaLabelsForCkeditor,
        }

        function fixAriaLabelsForCkeditor(textarea, editor) {
            textarea.nextElementSibling
                ?.querySelector(".ck-toolbar.ck-toolbar_grouping")
                ?.setAttribute("aria-label", "");

            editor.ui.getEditableElement()
                ?.setAttribute(
                    "aria-label",
                    RichTextEditor.getPlainText(editor)
                );
        }
    }();
    clickListen = function(){
        function activateClickTap()
        {
            activateClickListen();
        }
        function activateClickListen()
        {
            if(!rspkr.ui.Tools.ClickListen.active()){
                rspkr.ui.Tools.ClickListen.activate();
            }
        }

        function deactivateClickTap()
        {
            deactivateClickListen();
        }
        function deactivateClickListen()
        {
            if(rspkr.ui.Tools.ClickListen.active()){
                rspkr.ui.Tools.ClickListen.deactivate();
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
            if(rspkr.rs_tlc_play_started){
                return;
            }
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
            hidden_div.classList.add('prevent-select');
            hidden_div.setAttribute('wire:ignore','');
            textarea.parentNode.insertBefore(hidden_div,textarea);
            textarea.classList.add('hidden');
            textarea.classList.add('readspeaker_hidden_element');
            return hidden_div;
        }
        function createHiddenDivsForTextboxesCompletion(containerId) {
            var container = document.querySelector('#' + containerId);
            if (!container) {
                return;
            }
            var inputs = container._x_refs;
            if (!inputs) {
                return;
            }
            rspkr.rs_tlc_container = container;
            var inputsArray = Object.entries(inputs);
            for (var i = 0; i < inputsArray.length; i++) {
                createHiddenDivForElementAndHideElement(inputsArray[i][1]);
            }
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
            readable_div.classList.add('prevent-select');
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
            hidden_div.classList.add('prevent-select');
            element.classList.add('hidden');
            element.classList.add('rs_skip');
            element.classList.add('readspeaker_hidden_element');
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
            if(element.classList.contains('rs_skip')){
                element.classList.remove('rs_skip');
            }
        }
        return{
            removeOldElement:removeOldElement,
            removeReadableElements:removeReadableElements,
            cloneHiddenSpan:cloneHiddenSpan,
            displayHiddenElementsAndRemoveTheRest:displayHiddenElementsAndRemoveTheRest,
            getReadableDivForSelect:getReadableDivForSelect,
            createHiddenDivsForSelects:createHiddenDivsForSelects,
            createHiddenDivTextArea:createHiddenDivTextArea,
            createHiddenDivsForTextboxesCompletion
        }
    }();
    register = function(){
        function registerTlcClickListenActive()
        {
            rspkr.tlc_clicklisten_active = rspkr.ui.Tools.ClickListen.active();
        }
        return{
            registerTlcClickListenActive:registerTlcClickListenActive
        }
    }();
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
            hidden_div.style.position = 'absolute';
            hidden_div.classList.add('rs-click-listen');
            hidden_div.classList.add('rs-shadow-input');
            hidden_div.classList.add('form-input');
            hidden_div.classList.add('overflow-ellipsis');
            hidden_div.classList.add('prevent-select');
            obj.classList.remove('hidden');
            obj.classList.remove('readspeaker_hidden_element');
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
        function readCkeditor(event)
        {
            popup.hideRsTlcPopup(this);
            if(rspkr.rs_tlc_play_started){
                return;
            }
            clickListen.activateClickTap();
            rspkr.rs_tlc_play_started = false;
            rspkr.rs_tlc_prevent_close = true;
            rspkr.rs_tlc_prevent_ckeditor_focus = true;
            var element = this.linkedElement;
            if(element==null) {
                return;
            }
            // var elementClone = element.cloneNode(true);
            // element.replaceWith(elementClone);
            // window.classicEditorReplaced = false;
            //element.classList.add('rs_click_listen');
            //element = ckeditor.detachReadableAreaFromCkeditor(element.editorId);
            //element.contentEditable = false;
            element.click();
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
            doNotReadInput: doNotReadInput,
            readTextArea: readTextArea,
            readCkeditor: readCkeditor
        }
    }();
    popup = function(){
        function getRsbtnPopupTlc(questionId,event,correction)
        {
            var element = event.currentTarget;
            return getRsbtnPopupTlcElement(questionId,element,correction);

        }
        function alreadyThere(questionId)
        {
            var p = document.querySelector('.rsbtn_popup_tlc_'+questionId);
            if(p == null){
                return false;
            }
            if(p.classList.contains('hidden')){
                return false;
            }
            return true;
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
            var scrollCorrectionY = document.body.offsetHeight-window.innerHeight;
            switch (element.nodeName){
                case 'INPUT':
                case 'SELECT':
                    var span = element.nextElementSibling;
                    if(span.nodeName!='SPAN'){
                        return;
                    }
                    span.append(p);
                    p.style.left = correction.x+'px';
                    p.style.top = correction.y+'px';
                    break;
                case 'DIV':
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
        function rsRemovRsbtnPopupTlcForElement(ckeditorNode,questionId)
        {
            var p = document.querySelector('.rsbtn_popup_tlc_'+questionId);
            if(p == null){
                return;
            }
            setTimeout(hideRsTlcPopup.bind(null, p),500);
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
                case 'DIV':
                    p.removeEventListener('click',read.readCkeditor);
                    break;
                default:
                    p.removeEventListener('click');
            }

        }
        function positionBySelection()
        {
            s = window.getSelection();
            if(s.toString()==''){
                return;
            }
            oRange = s.getRangeAt(0); //get the text range
            oRect = oRange.getBoundingClientRect();
            document.querySelector('#rsbtn_popup').style.top = (oRect.y+window.scrollY+60)+'px';
            rspkr.rs_popup_modified = true;
        }
        return{
            getRsbtnPopupTlc:getRsbtnPopupTlc,
            rsRemovRsbtnPopupTlcForQuestion:rsRemovRsbtnPopupTlcForQuestion,
            hideRsTlcPopup:hideRsTlcPopup,
            getRsbtnPopupTlcElement:getRsbtnPopupTlcElement,
            rsRemovRsbtnPopupTlcForElement:rsRemovRsbtnPopupTlcForElement,
            alreadyThere:alreadyThere,
            positionBySelection
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
        function sliderForIpad()
        {
            if(!util.isIpadOS()){
                return;
            }
            var slider = document.querySelector('.rsbtn_speed_slider');
            if(slider.classList.contains('mouse-controlled')){
                return;
            }
            slider.classList.add('mouse-controlled');
        }
        return{
            hideRsPlayer:hideRsPlayer,
            showRsPlayer:showRsPlayer,
            startRsPlayer:startRsPlayer,
            sliderForIpad
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
            var readable_div = hiddenElement.getReadableDivForSelect(this.linkedElement);
            this.parentNode.insertBefore(readable_div,this);
            readable_div.style.position = 'absolute';
            popup.hideRsTlcPopup(this);
            readable_div.click();
        }
        function isIpadOS() {
            return [
                'iPad Simulator',
                'iPhone Simulator',
                'iPod Simulator',
                'iPad',
                'iPhone',
                'iPod'
            ].includes(navigator.platform) || (navigator.maxTouchPoints &&
                navigator.maxTouchPoints > 2 &&
                /MacIntel/.test(navigator.platform));
        }
        function isIOS12() {
            return(isIpadOS()&&!(navigator.maxTouchPoints &&
                navigator.maxTouchPoints > 2 &&
                /MacIntel/.test(navigator.platform)));
        }
        return{
            showById:showById,
            hideById:hideById,
            showByClassName:showByClassName,
            hideByClassName:hideByClassName,
            checkPossibleTextAreaValid:checkPossibleTextAreaValid,
            checkPossibleTextAreaAlreadyExists:checkPossibleTextAreaAlreadyExists,
            checkElementInActiveQuestion:checkElementInActiveQuestion,
            showReadableSelect:showReadableSelect,
            isIpadOS,
            isIOS12
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
        function detachReadableAreaFromCkeditor(editorId)
        {
            var editor = ClassicEditors[editorId];
            replaceReadableAreaByClone(editor);
            window.classicEditorDetached = true;
            return editor;
        }
        function replaceReadableAreaByClone(editor)
        {
            editor.currentElement  = editor.ui.view.editable.element;
            var element = editor.ui.view.editable.element;
            if(element) {
                var elementClone = element.cloneNode(true);
                elementClone.classList.add('ck-editor__editable_inline_replaced');
                element.replaceWith(elementClone);
            }
        }
        function reattachReadableAreaAndDestroy(editorId)
        {
            window.classicEditorDetached = false;
            var editor = ClassicEditors[editorId];
            if (editor) {
                var element = document.getElementsByClassName('ck-editor__editable_inline_replaced')[0];
                if(element){
                    element.replaceWith(editor.currentElement);
                    editor.destroy(true);
                }
            }
        }
        function addListenersForReadspeaker(editor,questionId,editorId)
        {
            var config = { attributes: true, childList: false, subtree: false };
            var element = editor.ui.view.editable.element;
            var callback = function(mutationsList, observer){
                for(var mutation of mutationsList) {
                    if (mutation.type === 'attributes') {
                        if(mutation.attributeName=='class'&&mutation.target.classList.contains('ck-focused')){
                            rsTlcEvents.handleCkeditorFocusForReadspeaker(mutation.target,questionId,editorId);
                        }else if(mutation.attributeName=='class'&&mutation.target.classList.contains('ck-blurred')){
                            if(mutation.target.ckeditorInstance&&mutation.target.ckeditorInstance.isReadOnly){
                                return;
                            }
                            rsTlcEvents.handleCkeditorBlurForReadspeaker(mutation.target,questionId,editorId);
                        }
                    }
                }
            }
            var observer = new MutationObserver(callback);
            observer.observe(element, config);
            rsTlcEvents.handleCkeditorSelectionChangeDoneForReadspeaker(editor);
            rsTlcEvents.handleCkeditorSelectionChangeForReadspeaker(editor);
            if(element.querySelector('figure')){
                rsTlcEvents.addListenerCkeditorTableCellFocusForReadspeaker(element.querySelector('figure'));
            }
            var config2 = { attributes: false, childList: true, subtree: false };
            var element2 = editor.ui.view.editable.element;
            var callback2 = function(mutationsList, observer2){
                for(var mutation of mutationsList) {
                    if (mutation.type != 'childList') {
                        continue;
                    }
                    if(typeof mutation.addedNodes=='undefined'){
                        continue;
                    }
                    if (typeof mutation.addedNodes[0]=='undefined'){
                        continue;
                    }
                    if(mutation.addedNodes[0].nodeName.toLowerCase()!='figure'){
                        continue;
                    }
                    rsTlcEvents.addListenerCkeditorTableCellFocusForReadspeaker(mutation.addedNodes[0]);
                }
            }
            var observer2 = new MutationObserver(callback2);
            observer2.observe(element2, config2);
        }
        return{
            disableContextMenuOnCkeditor:disableContextMenuOnCkeditor,
            shouldNotReinitCkeditor:shouldNotReinitCkeditor,
            detachReadableAreaFromCkeditor:detachReadableAreaFromCkeditor,
            addListenersForReadspeaker:addListenersForReadspeaker,
            reattachReadableAreaAndDestroy:reattachReadableAreaAndDestroy,
            replaceReadableAreaByClone
        }
    }();
    guard = function(){
        function shouldNotCreateHiddenTextarea(id)
        {
            var oldEl = document.getElementById('there_can_only_be_one');
            var possibleTextarea = false;
            var textareaId = 'editor-'+id;
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
            if(!window.classicEditorDetached){
                return true;
            }
            if(!util.checkElementInActiveQuestion(el)){
                return true;
            }
            return false;
        }
        function shouldNotDetachCkEditor(el)
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
            shouldNotReinitCkeditor:shouldNotReinitCkeditor,
            shouldNotDetachCkEditor
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
    // console.log('rs_tlc initialized!');
    rspkr.rs_tlc_play_started = false;
    ReadspeakerTlc.register.registerTlcClickListenActive();
    rspkr.rs_tlc_prevent_close = false;
    rspkr.rs_tlc_container = false;
    rspkr.rs_tlc_prevent_ckeditor_focus = false;
    rspkr.rs_tlc_ckeditor_selecting = false;
    rspkr.rs_popup_modified = false;
    ReadspeakerTlc.rsTlcEvents.handleIPadSelectionChange();
    ReadspeakerTlc.rsTlcEvents.addListenersToPopup();
});
window.rsConf = {
    general: {
        usePost: true,
        skipHiddenContent:true,
        customTransLangs: ['de_de', 'en', 'nl_nl', 'es_es']
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
            textmode : false,
            voicesettings: true
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
                // console.log('Player closed and callback fired!');
                ReadspeakerTlc.clickListen.deactivateClickTap();
                ReadspeakerTlc.player.hideRsPlayer();
                rspkr.rs_tlc_prevent_ckeditor_focus = false;
                window.document.dispatchEvent(new Event("readspeaker_closed", {
                    bubbles: true,
                    cancelable: true
                }));
                window.document.dispatchEvent(new Event("trigger_livewire_rerender", {
                    bubbles: true,
                    cancelable: true
                }));
            },
            stop: function() {
                // console.log('Player stopped and callback fired!');
                ReadspeakerTlc.clickListen.deactivateClickTap();
                rspkr.ui.getActivePlayer().close();
            },
            open: function() {
                // console.log('Open callback fired!');
                window.document.dispatchEvent(new Event("readspeaker_opened", {
                    bubbles: true,
                    cancelable: true
                }));
            },
            play: function() {
                // console.log('Play callback fired!');
                rspkr.rs_tlc_play_started = true;
                rspkr.rs_tlc_prevent_close = false;
                ReadspeakerTlc.player.showRsPlayer();
                window.document.dispatchEvent(new Event("readspeaker_started", {
                    bubbles: true,
                    cancelable: true
                }));
                ReadspeakerTlc.player.sliderForIpad();
            },
            pause: function() {
                // console.log('Pause callback fired!');
                rspkr.rs_tlc_play_started = false;
            }
        }
    }
};
window.classicEditorDetached = false;



