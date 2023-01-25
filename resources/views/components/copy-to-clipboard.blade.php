<div x-data="{
        copyToClipboard: async (params) => {
                if (!params.hasOwnProperty('message')) {
                    console.error('Copy to clipboard needs a value to copy.')
                    return;
                 }
                await $clipboard(params.message);
                if(params.notification) {
                    let notify = await $dispatch('notify', {message: params.notification});
                }
            }
    }"
    @copy-to-clipboard.window="await copyToClipboard($event.detail)"
></div>