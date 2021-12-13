<div id="login-body" class="flex justify-center items-center min-h-screen"
     x-init="$wire.handleDataAndRedirect();
            addRelativePaddingToBody('login-body', 10);

            "
     x-on:resize.window.debounce.200ms="addRelativePaddingToBody('login-body')"
     wire:ignore.self
>
    <div class="w-full max-w-[800px] mx-4 py-4">

        <div class="content-section p-10 space-y-5 shadow-xl grid grid-cols-1 content-center justify-center " style="min-height: 550px">
            <div>
                <div class="grid grid-cols-1 justify-center mb-2">
                    <svg class="justify-center-self animate-spin -ml-1 mr-3 h-5 w-5 text-primary" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="white" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
                <div>We zijn voor je aan het werk en verwerken je gegevens</div>
            </div>
        </div>
    </div>
</div>
