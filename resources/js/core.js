parent.skip = false;
let notifsent = false;
let lastLostFocus = {notification: false, delay: 3 * 60, reported: {}};
let alert = false;
let checkFocusTimer = false;

Core = {
    inApp : false,
    appType : '',

    init : function() {
        let isIOS = /(iPad|iPhone|iPod)/g.test(navigator.userAgent);
        let isAndroid = /Android/g.test(navigator.userAgent);

        if(isIOS) {
            Core.isIpad();
        }else if(isAndroid){
            Core.isAndroid();
        }

        runCheckFocus();
    },
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
    },
    isIpad : function() {
        var standalone = window.navigator.standalone,
            userAgent = window.navigator.userAgent.toLowerCase(),
            safari = /safari/.test( userAgent ),
            ios = /iphone|ipod|ipad/.test( userAgent );

        if( ios ) {
            if ( !standalone && safari ) {
                Core.appType = 'browser';
                Core.inApp = false;
            } else if ( standalone && !safari ) {
                Core.appType = 'standalone';
                Core.inApp = true;
            } else if ( !standalone && !safari ) {
                Core.appType = 'ipad';
                Core.inApp = true;
                checkForIpadKeyboard();
            };
        }
    },

    isAndroid : function() {
        Core.inApp = true;
        Core.appType = 'android';
    },
    isChromebook : function(){
        return (window.navigator.userAgent.indexOf('CrOS') > 0);
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

function checkForIpadKeyboard() {
    window.visualViewport.addEventListener('resize', function() {
        if(window.visualViewport.height < 400) {
            document.querySelector('header').classList.remove('fixed');
            document.querySelector('footer').classList.remove('fixed');
        } else {
            document.querySelector('header').classList.add('fixed');
            document.querySelector('footer').classList.add('fixed');
        }
    })
}