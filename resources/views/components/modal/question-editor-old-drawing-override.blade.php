<div id="old-drawing-question-modal"
     class="fixed inset-0 overflow-y-auto px-4 py-6 sm:px-0 z-[101]"
     x-data="{
        openEditor: async (button) => {
            let openDrawingTool = () => {
                showWarning = false;
                show = true;
            }
            if(!clearSlate) return openDrawingTool();
            button.disabled = true
            let clear = await $wire.clearQuestionBag();
            if (clear) return openDrawingTool();
            button.disabled = false;
        }
     }"
     x-show="showWarning"
     x-cloak
     x-transition:enter="ease-out duration-100"
     x-transition:enter-start="opacity-0 scale-90"
     x-transition:enter-end="opacity-100 scale-100"
     x-transition:leave="ease-in duration-100"
     x-transition:leave-start="opacity-100 scale-100"
     x-transition:leave-end="opacity-0 scale-90"
>
    <div x-show="showWarning" class="fixed inset-0 transform " x-on:click="showWarning = false"
         x-transition:enter="ease-out duration-100"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-out duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
    </div>
    <div x-show="showWarning"
         class="relative top-1/2 flex flex-col py-5 px-7 bg-white rounded-10 overflow-hidden shadow-xl transform -translate-y-1/2  max-w-xl sm:mx-auto"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
         x-transition:leave="ease-in duration-100"
         x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
         x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
        <div class="px-2.5 flex justify-between items-center mt-2">
            <h2>{{ __('drawing-modal.old-drawing-override-title') }}</h2>
            <x-icon.close class="cursor-pointer hover:text-primary" @click="showWarning = false"/>
        </div>
        <div class="divider mb-5 mt-2.5"></div>
        <div class="flex flex-1 h-full w-full px-2.5 body1 mb-5 space-x-2.5 ">
            <div class="flex flex-1 flex-col ">
                <span>{{ __('drawing-modal.old-drawing-override-body') }}</span>

                <div class="flex w-full justify-end mt-4 space-x-4">
                    <x-button.text class="rotate-svg-180" @click="showWarning = false">
                        <x-icon.chevron/>
                        <span>{{ __('auth.cancel') }}</span>
                    </x-button.text>
                    <x-button.primary @click="openEditor($el)">
                        <span>{{ __('auth.continue') }}</span>
                    </x-button.primary>
                </div>
            </div>
        </div>
    </div>

</div>
