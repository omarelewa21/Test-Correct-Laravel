<div x-data="{
        message: null,
        notification: null,
    }"
    @copy-to-clipboard.window="
        message = $event.detail.message;
        notification = $event.detail.notification;
    "
>
    <div x-data="{
            copyToClipboard: async () => {
                let copy = await $clipboard(message);
                if(copy) message = null;
                if(notification) {
                    let notify = await $dispatch('notify', {message: notification});
                    if(notify) notification = null;
                }
            }
        }"
        x-init="$watch('message', () => copyToClipboard())"
    ></div>
</div>