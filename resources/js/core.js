parent.skip = false;
let notifsent = false;
let lastLostFocus = {notification: false, delay: 3 * 60, reported: {}};
let alert = false;
let checkFocusTimer = false;

Core = {
    inApp: false,
    appType: '',
    inactive: 0,
    secondsBeforeStudentLogout: 60 * 60 * 3,
    devices: ['browser', 'electron', 'ios', 'chromebook'],

    init: function () {
        let isIOS = Core.detectIOS();
        let isAndroid = /Android/g.test(navigator.userAgent);
        let isChromebook = window.navigator.userAgent.indexOf('CrOS') > 0;

        let isFirefox = window.navigator.userAgent.indexOf('Firefox') > -1;

        if (isIOS) {
            Core.isIpad();
        } else if (isAndroid) {
            Core.isAndroid();
        } else if (isChromebook) {
            Core.isChromebook();
        }
        if (isFirefox) {
            Core.isFirefox();
        }

        Core.checkForElectron();

        runCheckFocus();
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
    },

    lostFocusWithoutReporting: function (text) {
        if (!isMakingTest()) {
            return;
        }

        let testtakemanager = document.querySelector("[testtakemanager]");
        if (testtakemanager != null) {
            livewire
                .find(testtakemanager.getAttribute("wire:id"))
                .shouldFraudNotificationsBeShown()
                .then(function (response) {
                    if (response.shouldFraudNotificationsBeShown) {
                        Notify.notify(text, "error");
                    }
                });
        }

        window.Livewire.emit("setFraudDetected");
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
    isFirefox: function () {
        document.querySelector('body').classList.add('firefox');
    },
    detectIOS: function () {
        let urlParams = new URLSearchParams(window.location.search);

        if(urlParams.get('device') !== null && urlParams.get('device') === 'ipad') {
            return true;
        }

        if (window.webview != null) return true;

        return false;
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
        let browserElements = document.querySelectorAll('[browser]');
        if (browserElements.length > 0) {
            browserElements.forEach((element) => {
                element.style.display = 'flex';
            })
        }
    },
    enableAppFeatures(appType) {
        let show = () => {
            let appElements = document.querySelectorAll('[' + appType + ']');
            appElements.forEach((element) => {
                element.style.display = 'flex';
            });
        }
        if(appType === 'chromebook'){
            window.onload = () => {
                try {
                    let count = 1;
                    let buttonDisplay = setInterval( () => {                         // send request to get the version, max 5 times then break the interval
                        let xhttp = new XMLHttpRequest();
                        xhttp.open("GET", "/get_app_version", true)
                        xhttp.send();
                        xhttp.onload = function() {
                            let response = JSON.parse(this.response).TLCVersion;    // get chrome version
                            if(response != 'x'){                                    // version is defined                              
                                if(response.charAt(0) == '2'){                      // version starts with 2.
                                    show();
                                }
                                else if(response.charAt(0) == '3'){                 // version starts with 3.
                                    chrome.runtime.sendMessage(                     // see if it is a kiosk app\
                                        document.getElementById("chromeos-extension-id").name,
                                        {isKiosk: true},
                                        function(response) {
                                            response.isKiosk ? show() : '';    // if koisk app, show button
                                        }
                                    )
                                }
                                clearInterval(buttonDisplay);

                            } else {
                                count >= 5 ? clearInterval(buttonDisplay) : count++;
                            }
                        }
                    }, 2000)
                } catch (error) {}
            }
        } else {                                                                    // version is not defined (headers is not recieved from the app yet)
            show();
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
    /**
     * Waits an interval time before logging user out
     * @param {boolean} firstLoad
     * @param {int} secondsBeforeTeacherLogout - default 15 min
     */
    startUserLogoutInterval(firstLoad = false, secondsBeforeTeacherLogout = 15 * 60  ) {
        let inactive = 0;
        document.addEventListener('mouseover', () => inactive = 0);
        document.addEventListener('keydown', () => inactive = 0);

        let startInterval = () => {
            let userLogoutInterval = setInterval(()=> {
                inactive++;
                if (inactive >= secondsBeforeTeacherLogout) {
                    clearInterval(userLogoutInterval);
                    Livewire.emit("openModal", "open-user-logout-warning-modal");
                }
            }, 1000);
        }

        if(firstLoad){
            window.onload = () => startInterval();
        }else{
            startInterval();
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
                if (Core.appType != 'electron' && Core.appType != 'ios') {
                    Core.lostFocus('lost-focus');
                }
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
