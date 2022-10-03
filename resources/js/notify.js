Notify = {
    notify: function (message, initialType, title = null) {
        let type = initialType ? initialType : 'info';
        window.dispatchEvent(new CustomEvent('notify', {detail: {message, type, title}}))
    }
}