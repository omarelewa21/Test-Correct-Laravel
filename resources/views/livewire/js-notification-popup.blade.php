<div
    x-data="{
        notify: async (event) => {
            let message = await $wire.getLocalizedMessage(event.detail.translation_key)
            let type = event.detail.type
            Notify.notify(message, type)
        }
    }"
    @js-notify-popup.window="notify($event)"
></div>