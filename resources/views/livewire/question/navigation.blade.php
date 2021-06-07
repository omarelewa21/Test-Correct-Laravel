<div class="flex flex-col pt-6 pb-4 space-y-10"
     test-take-player
     wire:key="navigation"
     x-data="{}"
     x-on:keydown.arrow-right.window="if(!isInputElement($event.target)) {$wire.nextQuestion()}"
     x-on:keydown.arrow-left.window="if(!isInputElement($event.target)) {$wire.previousQuestion()}"
     x-on:touchend.window="
        if(handleGesture($event.target) === 'right') $wire.previousQuestion();
        if(handleGesture($event.target) === 'left') $wire.nextQuestion();
     "
     x-on:wheel.window="
        if(handleScrollNavigation($event)) {
            if($event.wheelDelta > 0) {
                $wire.nextQuestion()
            } else {
                $wire.previousQuestion()
            }
        }
     "
>
    <x-partials.question-indicator wire:key="navi" :nav="$nav" :isOverview="$isOverview"/>

    <script>

    </script>
</div>
