require('./bootstrap');
require('alpinejs');
require('livewire-sortable');
require('./swipe');

addIdsToQuestionHtml = function () {
    let id = 1;
    let questionContainers = document.querySelectorAll('[questionHtml]');
    setTimeout(() => {
        questionContainers.forEach(function (item) {
            let decendents = item.querySelectorAll('*');
            decendents.forEach(function (decendent) {
                decendent.id = 'questionhtml_' + id;
                decendent.setAttribute('wire:key', 'questionhtml_' + id);
                id += 1;
            })
        })
    }, 1);
}


addRelativePaddingToBody = function (elementId, extraPadding = 0) {
    document.getElementById(elementId).style.paddingTop = (document.getElementById('header').offsetHeight + extraPadding) + 'px';
}
makeHeaderMenuActive = function (elementId) {
    document.getElementById(elementId).classList.add('active');
}

parent.skip = false;
let notifsent = false;
let lastLostFocus = {notification: false, delay: 3 * 60, reported: {}};
let alert = false;
let checkFocusTimer = false;

Notify = {
    notify: function (message, initialType) {
        let type = initialType ? initialType : 'info';
        window.dispatchEvent(new CustomEvent('notify', {detail: {message, type}}))
    }
}

runCheckFocus = function() {
    if (!checkFocusTimer) {
        checkFocusTimer = setInterval(checkPageFocus, 300);
    }
}

function checkPageFocus() {
    if (!parent.skip) {
        if (!document.hasFocus()) {
            if (!notifsent) {  // checks for the notifcation if it is already sent to the teacher
                console.log('lost focus from checkPageFocus');
                Core.lostFocus('lost-focus');
                notifsent = true;
            }
        } else {
            notifsent = false;  //mark it as not sent, to active it again
        }
    } else {
        window.focus();   //we need to set focus back to the window before changing skip value
        parent.skip = false;
    }
}

function shouldLostFocusBeReported(reason) {

    if (reason == null) {
        reason == "undefined";
    }

    if (!(reason in lastLostFocus.reported) || !alert) {
        lastLostFocus.reported[reason] = (new Date()).getTime() / 1000;
        return true;
    }

    let now = (new Date()).getTime() / 1000;
    let lastTime = lastLostFocus.reported[reason];
    if (lastTime <= now - lastLostFocus.delay) {
        lastLostFocus.reported[reason] = (new Date()).getTime() / 1000;
        return true;
    }

    window.Livewire.emit('checkConfirmedEvents', reason);

    return false;
}

Core = {
    lostFocus: function (reason) {
        if (reason == "printscreen") {
            Notify.notify('Het is niet toegestaan om een screenshot te maken, we hebben je docent hierover geïnformeerd', 'error');
        } else {
            Notify.notify('Het is niet toegestaan om uit de app te gaan', 'error');
        }

        window.Livewire.emit('setFraudDetected');

        if (shouldLostFocusBeReported(reason)) {
            livewire.find(document.querySelector('[testtakemanager]').getAttribute('wire:id')).call('createTestTakeEvent', reason);
        }
        alert = true;
    }
}

isInputElement = function(target) {
    return /^(?:input|textarea|select|button)$/i.test(target.tagName.toLowerCase());
}

handleScrollNavigation = function (evt) {
    if(evt.target.closest('#navigation-container') !== null) {
        return false;
    }
    return evt.shiftKey;
}
