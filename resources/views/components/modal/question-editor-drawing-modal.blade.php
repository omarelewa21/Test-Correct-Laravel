<div id="drawing-tool-modal"
     class="fixed inset-0 overflow-y-auto p-2.5 z-[101]"
     x-show="show"
     x-cloak
     x-transition:enter="ease-out duration-100"
     x-transition:enter-start="opacity-0 scale-90"
     x-transition:enter-end="opacity-100 scale-100"
     x-transition:leave="ease-in duration-100"
     x-transition:leave-start="opacity-100 scale-100"
     x-transition:leave-end="opacity-0 scale-90"
{{--     wire:ignore.self--}}
>
    <div class="fixed inset-0 transform " x-on:click="show = false; console.log(show)">
        <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
    </div>
    <div class="flex w-full h-full items-center align-center">
        <div
             class="relative flex flex-col bg-white rounded-10 overflow-hidden main-shadow w-full h-full"
             >
            <div class="flex flex-1 h-full w-full">
                <x-question.drawing.drawing-tool/>
            </div>
        </div>
    </div>
</div>