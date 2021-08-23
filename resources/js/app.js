require('./bootstrap');
require('livewire-sortable');
require('./swipe');
require('./core');
require('./notify');
require('./alpine');

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