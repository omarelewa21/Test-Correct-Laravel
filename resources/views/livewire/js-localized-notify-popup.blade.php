<div
    x-data="{
        notify: async (event) => {
            let message = await $wire.getLocalizedMessage(event.detail.translation_key)
            let type = event.detail.message_type
            Notify.notify(message, type)
        }
    }"
    x-on:js-localized-notify-popup.window="notify($event)"
></div>