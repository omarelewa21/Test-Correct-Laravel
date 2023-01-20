<div id="co-learning-start-screen">
    <x-partials.header.co-learning-start-teacher test-name="{{ $testTake->test->name ?? '' }}"/>
    <div class="co-learning-start-content">

        <x-button.cta wire:click="goToNewCoLearning">Start NEW Co-Learning OPEN_ONLY</x-button.cta>
        <x-button.primary  wire:click="goToOldCoLearning">Start OLD Co-Learning ALL QUESTIONS</x-button.primary>
    </div>
</div>
