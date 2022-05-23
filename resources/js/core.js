parent.skip = false;
let notifsent = false;
let lastLostFocus = {notification: false, delay: 3 * 60, reported: {}};
let alert = false;
let checkFocusTimer = false;

Core = {
    inApp: false,
    appType: '',
    inactive: 0,
    secondsBeforeStudentLogout: 60 * 60,
    devices: ['browser', 'electron', 'ios', 'chromebook'],

    init: function () {
        let isIOS = Core.detectIOS();
        let isAndroid = /Android/g.test(navigator.userAgent);
        let isChromebook = window.navigator.userAgent.indexOf('CrOS') > 0;

        if (isIOS) {
            Core.isIpad();
        } else if (isAndroid) {
            Core.isAndroid();
        } else if (isChromebook) {
            Core.isChromebook();
        }

        Core.checkForElectron();

        runCheckFocus();
        catchscreenshotchromeOS();
        startStudentActivityCheck();

        Core.appType === '' ? Core.enableBrowserFeatures() : Core.enableAppFeatures(Core.appType);
    },
    lostFocus: function (reason) {
        if (!isMakingTest()) {
            return;
        }

        var testtakemanager = document.querySelector('[testtakemanager]');
        if (testtakemanager != null) {
            livewire
                .find(testtakemanager.getAttribute('wire:id'))
                .shouldFraudNotificationsBeShown()
                .then(function (response) {
                   if (response.shouldFraudNotificationsBeShown) {
                       if (reason == "printscreen") {
                           Notify.notify('Het is niet toegestaan om een screenshot te maken, we hebben je docent hierover geïnformeerd', 'error');
                       } else if (reason == 'illegal-programs') {
                           Notify.notify('Er staan applicaties op de achtergrond aan die niet zijn toegestaan', 'error');
                       } else {
                           Notify.notify('Het is niet toegestaan om uit de app te gaan', 'error');
                       }
                   }
                });
            if (shouldLostFocusBeReported(reason)) {
                livewire.find(testtakemanager.getAttribute('wire:id')).call('createTestTakeEvent', reason);
            }
        }

        window.Livewire.emit('setFraudDetected');

        alert = true;
    },
    isIpad: function () {
        // var standalone = window.navigator.standalone,
        //     userAgent = window.navigator.userAgent.toLowerCase(),
        //     safari = /safari/.test(userAgent),
        //     ios = /iphone|ipod|ipad/.test(userAgent);
        Core.appType = 'ios';

        // if (ios) {
        //     if (!standalone && safari) {
        //         Core.appType = 'browser';
        //         Core.inApp = false;
        //     } else if (standalone && !safari) {
        //         Core.appType = 'standalone';
        //         Core.inApp = true;
        //     } else if (!standalone && !safari) {
        //         Core.appType = 'ipad';
        //         Core.inApp = true;
        //     }
        // }
    },

    isAndroid: function () {
        Core.inApp = true;
        Core.appType = 'android';
    },
    isChromebook: function () {
        Core.inApp = true;
        Core.appType = 'chromebook';
    },
    detectIOS: function () {
        let urlParams = new URLSearchParams(window.location.search);

        if(urlParams.get('device') !== null && urlParams.get('device') === 'ipad') {
            return true;
        }
    },
    disableDeviceSpecificFeature(){
        Core.devices.forEach((device) => {
            let deviceElements = document.querySelectorAll('['+device+']');
            if (deviceElements.length > 0) {
                deviceElements.forEach((element) => {
                    element.style.display = 'none';
                });
            }
        });
    },
    enableBrowserFeatures() {
        console.log('In enableBrowserFeatures');
        let browserElements = document.querySelectorAll('[browser]');
        if (browserElements.length > 0) {
            browserElements.forEach((element) => {
                element.style.display = 'flex';
            })
        }
    },
    enableAppFeatures(appType) {
        console.log('In enableAppFeatures');
        if(appType !== 'chromebook'){
            let appElements = document.querySelectorAll('[' + appType + ']');
            appElements.forEach((element) => {
                element.style.display = 'flex';
            });
        }else{
            Core.showCloseButtonChromeOS()
        }
    },
    checkForElectron() {
        try {
            if (typeof (electron.closeApp) === typeof (Function)) {
                Core.appType = 'electron';
            }
        } catch (error) {
        }
    },
    closeElectronApp() {
        Core.closeApplication('close');
    },
    closeChromebookApp(portalUrl) {
        try {
            chrome.runtime.sendMessage(
                document.getElementById("chromeos-extension-id").name,
                { close: true }
            );
        } catch (error) {}
        window.location = portalUrl+'logout';
    },
    closeApplication(cmd) {
        try {
            chrome.runtime.sendMessage(
                document.getElementById("chromeos-extension-id").name,
                { close: true }
            );
        } catch (error) {}
        if (cmd == 'quit') {
            open('/login', '_self').close();
        } else if (cmd == 'close') {
            try {
                electron.closeApp();
            } catch (error) {
                window.close();
            }
        }
        return false;
    },
    setAppTestConfigIfNecessary(participantId) {
        try {
            electron.setTestConfig(participantId);
            fetch('test_takes/take/');
        } catch (error) {}
        try {webview.setTestConfig(participantId);} catch (error) {}
    },
    changeAppTypeToIos()
    {
        Core.appType = 'ios'
        Core.disableDeviceSpecificFeature();
    },
    showCloseButtonChromeOS()
    {
        // const xhttp = new XMLHttpRequest();
        // xhttp.onload = function() {
        //     if( this.app_type.toLowerCase() === 'chromeos' && this.responseText.app_version.charAt(0) === '2' )
        //     {
        //         let appElements = document.querySelectorAll('[chromebook]');
        //         appElements.forEach((element) => {
        //             element.style.display = 'flex';
        //         });
        //     }
        // }
        // xhttp.open("GET", "/appVersion", true);
        // xhttp.send();
        try {
            chrome.runtime.getManifest().version;
            console.log('Success', chrome.runtime.getManifest().version);
            let appElements = document.querySelectorAll('[chromebook]');
            appElements.forEach((element) => {
                element.style.display = 'flex';
            });
        } catch (error) {
            console.log('Failed');
            chrome.runtime.getManifest().version;
        }
    }
}

runCheckFocus = function () {
    if (!checkFocusTimer) {
        checkFocusTimer = setInterval(checkPageFocus, 300);
    }
}

function checkPageFocus() {
    if (!parent.skip) {
        if (!document.hasFocus()) {
            if (!notifsent) {  // checks for the notifcation if it is already sent to the teacher
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
    document.addEventListener('click', function (e) {
        if (needsKeyboard(e.target)) {
            document.querySelector('header').classList.remove('fixed');
            document.querySelector('footer').classList.remove('fixed');
        } else {
            document.querySelector('header').classList.add('fixed');
            document.querySelector('footer').classList.add('fixed');
        }
    })
}

function needsKeyboard(target) {
    return /^(?:input|textarea)$/i.test(target.tagName.toLowerCase());
}

function startStudentActivityCheck() {
    document.addEventListener('mousemove', function () {
        Core.inactive = 0;
    })
    document.addEventListener('touchstart', function () {
        Core.inactive = 0;
    })

    studentActivityTimer = setInterval(function () {
        Core.inactive++;
        if (Core.inactive >= Core.secondsBeforeStudentLogout) {
            Livewire.emit('studentInactive');
        }
    }, 1000);
}
function isMakingTest() {
    return document.querySelector('[testtakemanager]') != null;
}

function catchscreenshotchromeOS(){
   if(Core.appType == 'chromebook') {
        let safeKeys = ['c', 'x', 'z', 'y', 'v','0']
        let storeKeys = [];
    
        window.addEventListener("keydown", (event)=> {
            if(event.ctrlKey && !event.repeat){
                storeKeys.push(event.key);
            }
        });
    
        window.addEventListener("keyup", (event)=> {
            if(event.key == "Control"){
                for(key of storeKeys){
                    if(!safeKeys.includes(key.toLowerCase()) && key != "Control"){
                        Core.lostFocus('printscreen');  //massage to teacher needs to added
                        break;
                    }
                }
                if(storeKeys.length == 1 & storeKeys[0] == "Control"){
                    Core.lostFocus('printscreen'); //massage to teacher needs to added
                }
                storeKeys = [];
            }
        });
     }    
}