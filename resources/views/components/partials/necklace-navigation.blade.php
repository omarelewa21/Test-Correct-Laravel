<div class="flex w-full h-full px-15 items-center invisible overflow-hidden"
     id="necklace-navigation"
     x-data="reviewNavigation(@js($position))"
     x-bind:class="{'invisible': !initialized }"
     x-on:resize.window.throttle="resize()"
     wire:ignore.self
>
    <div class="slider-buttons left | flex relative pt-4 -top-px h-full z-10"
         x-show="showSlider">
        <button class="start | inline-flex base rotate-svg-180 w-8 h-8 rounded-full transition items-center justify-center transform focus:outline-none"
                x-on:click="start()">
            <x-icon.arrow-last />
        </button>
        <button class="left | inline-flex base rotate-svg-180 w-8 h-8 rounded-full transition items-center justify-center transform focus:outline-none"
                x-on:click="left()">
            <x-icon.chevron />
        </button>
    </div>
    <div id="navscrollbar"
         class="question-indicator gap-2 pt-4 h-full"
         x-bind:class="{'overflow-x-auto px-3' : showSlider}"
    >
        {{ $loopSlot }}
    </div>
    <div class="slider-buttons right | flex relative pt-4 -top-px h-full z-10"
         x-show="showSlider">
        <button class="right | inline-flex base w-8 h-8 rounded-full transition items-center justify-center transform focus:outline-none"
                x-on:click="right()">
            <x-icon.chevron />
        </button>
        <button class="end | inline-flex base w-8 h-8 rounded-full transition items-center justify-center transform focus:outline-none"
                x-on:click="end()">
            <x-icon.arrow-last />
        </button>
    </div>
</div>
