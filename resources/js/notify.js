Notify = {
    notify: function (message, initialType) {
        let type = initialType ? initialType : 'info';
        window.dispatchEvent(new CustomEvent('notify', {detail: {message, type}}))
    }
}