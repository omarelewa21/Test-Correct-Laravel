<div
    x-data="{
        notify: async (event) => {
            let message = await $wire.getLocalizedMessage(event.detail.message)
            let type = event.detail.type
            Notify.notify(message, type)
        }
    }"
    @notify-js.window="notify($event)"
></div>