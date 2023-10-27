<div class="flex flex-col pt-6 pb-4 space-y-10"
     test-take-player
     wire:key="navigation"
     x-data="{showLoader: false}"
     x-on:show-loader.window="showLoader = true; if('route' in $event.detail) { $wire.redirectTo($event.detail.route) }"
     @if(!$isOverview)
     x-on:keydown.arrow-right.window="if(!isInputElement($event.target)) {$wire.call('nextQuestion')}"
     x-on:keydown.arrow-left.window="if(!isInputElement($event.target)) {$wire.call('previousQuestion')}"
     
     x-on:wheel.window="
        if(handleScrollNavigation($event)) {
            if($event.wheelDelta > 0) {
                $wire.call('nextQuestion')
            } else {
                $wire.call('previousQuestion')
            }
        }
     "
        @endif
>
    <x-partials.question-indicator wire:key="navi" :nav="$nav" :isOverview="$isOverview"/>


    <div x-cloak
         x-show="showLoader"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-75"
         id="loading"
         class="w-full h-full fixed block top-0 left-0 bg-white opacity-75 "
         style="z-index: 100;margin-top: 0">
         <span class="opacity-75 top-1/2 my-0 mx-auto block relative flex justify-center" style="top: 50%;">
             <img class="flex" src="/img/loading.gif" alt="loading"/>
         </span>
    </div>
</div>
