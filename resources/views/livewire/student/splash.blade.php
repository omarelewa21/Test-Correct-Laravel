<div id="login-body" class="flex justify-center items-center min-h-screen"
     x-init="setTimeout(function() {$wire.handleDataAndRedirect();},2500);"
     wire:ignore.self
>
    <div class="w-full max-w-[800px] mx-4 py-4">

        <div class="content-section p-10 space-y-5 shadow-xl grid grid-cols-1 content-center justify-center " style="min-height: 550px">
            <div>
                <div class="flex  justify-center mb-5">
                    <x-tc-connect-with-entree class="h-80 w-80" />
                </div>
                <div class="flex justify-center">We zijn voor je aan het werk en verwerken je gegevens</div>
            </div>
        </div>
    </div>
</div>
