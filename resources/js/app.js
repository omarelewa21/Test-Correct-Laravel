require('./bootstrap');
require('./swipe');
require('./livewire-sortablejs');
require('./core');
require('./notify');
require('./alpine');
require('./rich-text-editor');
require('./drawing/drawing-question');
require('./readspeaker_app');
require('./attachment');
require('./flatpickr');
require('./navigation-bar');
require('../../vendor/wire-elements/modal/resources/js/modal');
require('./webspellchecker_tlc');
require('./pdf-download');


window.ClassicEditors = [];

makeHeaderMenuActive = function (elementId) {
    document.getElementById(elementId).classList.add('active');
}

addCSRFTokenToEcho = function (token) {
 if(typeof Echo.connector.pusher.config.auth !== 'undefined') {
     Echo.connector.pusher.config.auth.headers['X-CSRF-TOKEN'] = token;
 }
}

isInputElement = function(target) {
    if(/^(?:input|textarea|select|button)$/i.test(target.tagName.toLowerCase())){
        return true;
    }
    if(typeof target.ckeditorInstance != "undefined"){
        return true;
    }
    if((typeof ReadspeakerTlc != 'undefined')&&rsPageContainsCkeditor()){
        return true;
    }
    return false;
}

rsPageContainsCkeditor = function() {
    if(typeof ReadspeakerTlc == 'undefined'){
        return false;
    }
    var questionContainer = document.querySelector('.rs_readable');
    if(questionContainer == null){
        return false;
    }
    var ckeditorNode = questionContainer.querySelector('.ck-editor__editable');
    if(ckeditorNode != null){
        return true;
    }
    return false;
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

countPresentStudents = function (members)
{
    var activeStudents = 0;
    members.each((member) => {
        if (member.info.student) {
            activeStudents++;
        }
    })

    return activeStudents;
}

addTitleToImages = function(selector,title)
{
    var container = document.querySelector(selector);
    if(container != null){
        var images = container.querySelectorAll('img');
        images.forEach(function(image) {
            if(image.title==null||image.title==''){
                image.title = title;
            }
        });
    }
}

String.prototype.contains = function (text)
{
    if (text === '') return false;
    return this.includes(text);
}

getClosestLivewireComponentByAttribute = function (element, attributeName) {
    return livewire.find(element.closest(`[${attributeName}]`).getAttribute('wire:id'));
}

String.prototype.capitalize = function ()
{
    return this.charAt(0).toUpperCase() + this.slice(1);
}

clearClipboard = function () {
    //source: https://stackoverflow.com/a/30810322
    function fallbackCopyTextToClipboard(text) {
        var textArea = document.createElement("textarea");
        textArea.value = text;

        // Avoid scrolling to bottom
        textArea.style.top = "0";
        textArea.style.left = "0";
        textArea.style.position = "fixed";

        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();

        try {
            var successful = document.execCommand("copy");
            var msg = successful ? "successful" : "unsuccessful";
        } catch (err) {
        }

        document.body.removeChild(textArea);
    }
    function copyTextToClipboard(text) {
        return new Promise((resolve, reject) => {
            if (!navigator.clipboard) {
                fallbackCopyTextToClipboard(text);
                resolve();
            }
            navigator.clipboard.writeText(text).then(() => {
                resolve();
            }).catch(() => { fallbackCopyTextToClipboard(text); resolve(); });
        });
    }

    return copyTextToClipboard(' ');
}

preventNavigationByKeydown = function(event)
{
    return event.stopPropagation();
}

livewireMessageContainsModelName = (message, modelName) => {
    return message.updateQueue.map(queue => {

        if(typeof queue.payload?.name !== 'undefined') {
            return queue.payload.name?.includes(modelName)
        }
        return String(queue.payload?.params[0])?.includes(modelName)
    })[0];
}

questionCardOpenDetailsModal = (questionUuid, inTest) => {
    Livewire.emit(
        'openModal',
        'teacher.question-detail-modal',
        {questionUuid, inTest}
    );
}
questionCardOpenGroup = (element, questionUuid, inTest) => {
    element.closest('[group-container]')
        .dispatchEvent(
            new CustomEvent(
                'show-group-details',
                {detail: {questionUuid, inTest } }
            )
        );
}

addQuestionToTestFromTestCard = (button, questionUuid, showQuestionBankAddConfirmation) => {
    document.querySelector('#question-bank')
        .dispatchEvent(
            new CustomEvent(
                'add-question-to-test',
                {
                    detail: {
                        button,
                        questionUuid,
                        showQuestionBankAddConfirmation
                    }
                }
            )
        )
}