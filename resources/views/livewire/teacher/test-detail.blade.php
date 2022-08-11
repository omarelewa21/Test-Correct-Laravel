<div id="test-detail"
     class="flex flex-col relative w-full min-h-full bg-lightGrey border-secondary mt-12"
     x-data="{groupDetail: null, bodyVisibility: true,  maxHeight: '100%'}"
     x-init="
     groupDetail = $el.querySelector('#groupdetail');
     showGroupDetails = async (groupQuestionUuid) => {
            let readyForSlide = await $wire.showGroupDetails(groupQuestionUuid);

            if (readyForSlide) {
                groupDetail.style.left = 0;
                document.documentElement.scrollTo({top: 0, behavior: 'smooth'});
                maxHeight = groupDetail.offsetHeight + 'px';
                $nextTick(() => {
                    setTimeout(() => bodyVisibility = false, 250);
                })

            }
        }

        closeGroupDetail = () => {
            bodyVisibility = true;
            maxHeight = groupDetail.style.left = '100%';
            $nextTick(() => $wire.clearGroupDetails() );
        }
     "
     :style="`max-height: ${maxHeight}`"
     wire:init="handleReferrerActions()"
>
    <div class="flex w-full border-b border-secondary pb-1">
        <div class="flex w-full justify-between">
            <div class="flex items-center space-x-2.5">
                <x-button.back-round wire:click="redirectToTestOverview"/>
                <div class="flex text-lg bold">
                    <span>{{ __('Toets') }}: {{ $this->test->name }}</span>
                </div>
            </div>

        </div>

    </div>
    <div class="flex w-full justify-between mt-3 items-center">
        <div class="flex space-x-2.5">
            <div class="bold">{{ $this->test->subject->name }}</div>
            <div class="italic">{{ $this->test->abbreviation }}</div>
            <div>{{ $this->test->authors_as_string }}</div>
        </div>
        <div class="flex note text-sm">
            <span>{{ __('general.Laatst gewijzigd') }}: {{ \Carbon\Carbon::parse($this->test->updated_at)->format('d/m/\'y') }}</span>
        </div>
    </div>
    <div class="flex w-full justify-between mt-1 note text-sm">
        <div class="flex">
            <span class="text-sm">{{ trans_choice('cms.vraag', $this->amountOfQuestions['regular']) }}, {{ trans_choice('cms.group-question-count', $this->amountOfQuestions['group']) }}</span>
        </div>
    </div>

    <div
            class="flex w-full justify-end mt-3 note text-sm space-x-2.5"


    >
        <x-actions.test-delete :uuid="$this->test->uuid"/>

        <x-actions.test-open-settings :uuid="$this->uuid"/>

        <x-actions.test-open-edit :uuid="$this->uuid"/>

        <x-actions.test-open-preview :uuid="$this->uuid"/>

        <livewire:actions.test-make-pdf :uuid="$this->uuid"/>
        <livewire:actions.test-duplicate-test :uuid="$this->uuid"/>
        <livewire:actions.test-plan-test :uuid="$this->uuid"/>
    </div>
    <div class="flex w-full" x-show="bodyVisibility">
        <div class="w-full mx-auto divide-y divide-secondary">
            {{-- Content --}}
            <div class="flex flex-col py-4" style="min-height: 500px">
                <x-grid class="mt-4">
                    @foreach(range(1, 6) as $value)
                        <x-grid.loading-card :delay="$value"/>
                    @endforeach

                    @foreach($this->test->testQuestions->sortBy('order') as $testQuestion)
                        {{--<x-grid.question-card :question="$testQuestion->question" />--}}
                        <x-grid.question-card-detail :testQuestion="$testQuestion"/>
                    @endforeach
                </x-grid>
            </div>
        </div>
    </div>
    <x-notification/>
    <div id="groupdetail" wire:ignore.self style="min-height: 100%;">
        <div class="max-w-5xl lg:max-w-7xl mx-auto">
            @if($this->groupQuestionDetail != null)
                <x-partials.group-question-details :groupQuestion="$this->groupQuestionDetail" context="testdetail"/>
            @endif
        </div>
    </div>
</div>
