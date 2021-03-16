<div class="flex flex-col items-end mb-3 bg-white p-2 rounded-10" x-show.transition="progressBar">
    <span class="mb-1" x-text="`${progress} {{__('test_take.seconds_left')}}`"></span>
    <span class="p-3 w-full rounded-md bg-gray-200 overflow-hidden relative flex items-center">
            <span class="absolute h-full w-full bg-primary left-0 transition-all duration-300"
                  :style="`width: ${ progress/startTime * 100 }%`"></span>
        </span>
</div>