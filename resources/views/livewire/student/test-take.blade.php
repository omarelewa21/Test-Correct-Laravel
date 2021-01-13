<div class="w-full">
    <div class="flex mb-12">
        <x-partials.question-indicator :questions="$testTake->test->testQuestions"></x-partials.question-indicator>
    </div>

    <div class="bg-white rounded-10 p-8 sm:p-10 content-section">
        <div class="question-title question-indicator border-bottom mb-6">
            <div class="inline question-number rounded-full text-center complete">
                <span class="align-middle">5</span>
            </div>
            <h1 class="inline-block mr-6">{{ $mainQuestion->type }}</h1>
            <h4 class="inline-block">{{$mainQuestion->score}}pt</h4>
        </div>
        <div>
            <livewire:$component :question="$mainQuestion->question" :key="$mainQuestion->uuid"/>
        </div>
    </div>
</div>
