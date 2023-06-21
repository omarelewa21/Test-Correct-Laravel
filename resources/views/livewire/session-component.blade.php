<div x-init="
        storeToSession = (params) => {
            if (!params || Object.keys(params).length === 0) {
                return;
            }
            $wire.storeToSession(params); 
        }
    "
    @store-to-session.window="storeToSession($event.detail)"
></div>