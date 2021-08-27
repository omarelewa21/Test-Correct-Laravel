require('./bootstrap');
require('alpinejs');
require('livewire-sortable');
require('./swipe');
require('./core');
require('./notify');

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

isInputElement = function(target) {
    return /^(?:input|textarea|select|button)$/i.test(target.tagName.toLowerCase());
}

handleScrollNavigation = function (evt) {
    if(evt.target.closest('#navigation-container') !== null) {
        return false;
    }
    return evt.shiftKey;
}

truncateOptionsIfTooLong = function (el) {
    let options = el.querySelectorAll('option');
    if (options !== null) {
        let truncateLimit;
        if (window.innerWidth < 900) truncateLimit = 80;
        if (window.innerWidth >= 900 && window.innerWidth < 1200) truncateLimit = 110;
        if (window.innerWidth >= 1200) truncateLimit = 180;

        options.forEach(function (option) {
            if (option.value.length > truncateLimit) {
                option.text = option.value.slice(0, truncateLimit) + '...';
            }
        });
    }
}

setTitlesOnLoad = function (el) {
    let selects = el.querySelectorAll('select');
    if (selects !== null) {
        selects.forEach(function (select) {
            if (select.value !== '') {
                select.setAttribute('title', select.value);
            }
        });
    }
    let inputs = el.querySelectorAll('input');
    if (inputs !== null) {
        inputs.forEach(function (input) {
            if (input.value !== '') {
                input.setAttribute('title', input.value);
            }
        });
    }
}

initializeIntenseWrapper = function (app_key, debug, deviceId, sessionId, code) {
    addScript('https://education.intense.solutions/collector/latest.uncompressed.js');

    var initializeInterval = setInterval(function() {
        if (typeof IntenseWrapper !== 'undefined' ) {
            Intense = new IntenseWrapper({
                api_key: app_key, // This is a public key which will be provided by Intense.
                app: 'name of the app that implements Intense. example: TC@1.0.0',
                debug: debug // If true, all debug data will be written to console.log().
            })
                .onError(function (e, msg) {

                    // So far, the only available value for 'msg' is 'unavailable', meaning that the given interface/method cannot be used.
                    // If no error handler is registered, all errors will be written to console.log.

                    switch (e) {
                        case 'start':
                            console.log('Intense: Could not start recording because it was ' + msg);
                            break;
                        case 'pause':
                            console.log('Intense: Could not pause recording because it was ' + msg);
                            break;
                        case 'resume':
                            console.log('Intense: Could not resume recording because it was ' + msg);
                            break;
                        case 'end':
                            console.log('Intense: Could not end recording because it was ' + msg);
                            break;
                        case 'network':
                            console.log('Intense: Could not send data over network because it was ' + msg);
                            break;
                        default:
                            console.log('Intense: Unknown error occured!');
                    }

                }).onData(function (data) {
                    // This function is called when data is sent to the Intense server. data contains the data that is being sent.
                    console.log('Data sent to Intense', data);
                }).onStart(function () {
                    console.log('Intense started recording');
                }).onPause(function () {
                    console.log('Intense paused recording');
                }).onResume(function () {
                    console.log('Intense resumed recording');
                }).onEnd(function () {
                    console.log('Intense ended recording');
                });


            /** devivceId = userId, sessionId = $testParticipantId; **/
            //Intense.resetDefaults();
            Intense.start(deviceId.toString(), sessionId.toString(), code);

            clearInterval(initializeInterval);
        }
    }, 2000)

    function addScript(src) {
        var s = document.createElement('script');
        s.setAttribute('src', src);
        document.body.appendChild(s);
    }
}

dragElement = function (element) {
    var pos1 = 0, pos2 = 0, pos3 = 0, pos4 = 0;

    if (document.getElementById(element.id + "drag")) {
        // if present, the header is where you move the DIV from:
        document.getElementById(element.id + "drag").onmousedown = dragMouseDown;
        document.getElementById(element.id + "drag").ontouchstart = dragMouseDown;
    } else {
        // otherwise, move the DIV from anywhere inside the DIV:
        element.onmousedown = dragMouseDown;
    }

    function dragMouseDown(e) {
        e = e || window.event;
        // get the mouse cursor position at startup:
        if (e.type === 'touchstart') {
            pos3 = e.touches[0].clientX;
            pos4 = e.touches[0].clientY;
        } else {
            pos3 = e.clientX;
            pos4 = e.clientY;
        }
        document.onmouseup = closeDragElement;
        document.ontouchend = closeDragElement;
        // call a function whenever the cursor moves:
        document.onmousemove = elementDrag;
        document.ontouchmove = elementDrag;
    }

    function elementDrag(e) {
        e = e || window.event;

        // calculate the new cursor position:
        if (e.type === 'touchmove') {
            pos1 = pos3 - e.touches[0].clientX;
            pos2 = pos4 - e.touches[0].clientY;
            pos3 = e.touches[0].clientX;
            pos4 = e.touches[0].clientY;
        } else {
            pos1 = pos3 - e.clientX;
            pos2 = pos4 - e.clientY;
            pos3 = e.clientX;
            pos4 = e.clientY;
        }
        // set the element's new position:
        element.style.top = (element.offsetTop - pos2) + "px";
        element.style.left = (element.offsetLeft - pos1) + "px";

    }

    function closeDragElement() {
        // stop moving when mouse button is released:
        document.onmouseup = null;
        document.ontouchend = null;
        document.onmousemove = null;
        document.ontouchmove = null;
    }
}