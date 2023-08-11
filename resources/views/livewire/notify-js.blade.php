<div
    x-data="{
        notify: async (event) => {
            console.log(event.detail);
            let message = await $wire.getLocalizedMessage(event.detail.message)
            let type = event.detail.type
            console.log(message, type)
            Notify.notify(message, type)
        }
    }"
    @notify-js.window="notify($event)"
></div>