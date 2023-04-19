<div id="test-detail"
     class="flex flex-col w-full min-h-full bg-lightGrey border-secondary "
     x-data="{groupDetail: null, groupDetailActive: false, testDetailBodyVisibility: true,  maxHeight: '100%'}"
     x-init="
     groupDetail = $el.querySelector('#groupdetail');
     showGroupDetails = async (groupQuestionUuid, inTest) => {
            let readyForSlide = await $wire.showGroupDetails(groupQuestionUuid, inTest);

            if (readyForSlide) {
                groupDetailActive = true;
                document.documentElement.scrollTo({top: 0, behavior: 'smooth'});
                maxHeight = groupDetail.offsetHeight + 'px';
                $nextTick(() => {
                    setTimeout(() => testDetailBodyVisibility = false, 250);
                })

            }
        }

        closeGroupDetail = () => {
            if(!testDetailBodyVisibility) {
                testDetailBodyVisibility = true;
                groupDetailActive = false;
                maxHeight = groupDetail.style.left = '100%';
                $nextTick(() => $wire.clearGroupDetails() );
            }
        }
        $nextTick(() => $dispatch('test-questions-ready'));
     "
     :style="`max-height: ${maxHeight}`"
     @empty($this->mode)
        wire:init="handleReferrerActions()"
     @endempty
     group-container
     x-on:show-group-details="showGroupDetails($event.detail.questionUuid, $event.detail.inTest );"
     x-on:close-group-details="closeGroupDetail()"
     wire:key="test-detail-{{ $this->uuid }}"
>
    <div class="flex w-full border-b border-secondary pb-1 sticky bg-lightGrey z-1 sticky-pseudo-bg"
         :style="{top: $root.offsetTop + 'px'}">

        <div class="w-full max-w-screen-2xl mx-auto px-10 z-1 py-1">
            <div class="flex w-full justify-between">
                <div class="flex items-center space-x-2.5 w-full">
                    @empty($this->mode)
                        <x-button.back-round class="shrink-0" wire:click="redirectToTestOverview"/>
                    @endempty
                    @if(isset($this->mode) && $this->mode === 'cms')
                        <x-button.back-round class="shrink-0" x-on:click="closeTestSlide"/>
                    @endif
                    <div class="flex text-lg bold w-[calc(100%-50px)]">
                        <span class="truncate" selid="test-detail-title">{{ __('Toets') }}: {{ $this->test->name }}</span>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="w-full max-w-screen-2xl mx-auto px-10">
        <div class="flex w-full justify-between mt-3 items-center">
            <div class="flex space-x-2.5">
                <div class="bold">{{ $this->test->subject->name }}</div>
                <div class="italic">{{ $this->test->abbreviation }}</div>
                <div>{{ $this->test->authors_as_string }}</div>
            </div>
            <div class="flex flex-col note text-sm gap-1">
                <span>{{ __('general.Laatst gewijzigd') }}: {{ \Carbon\Carbon::parse($this->test->updated_at)->format('d/m/\'y') }}</span>
                <span class="ml-auto"><x-published-tag :published="$this->test->isPublished()"/></span>
            </div>
        </div>
        <div class="flex w-full justify-between mt-1 note text-sm">
            <div class="flex space-x-2.5">
                <div class="text-sm">{{ trans_choice('cms.vraag', $this->amountOfQuestions['regular']) }}, {{ trans_choice('cms.group-question-count', $this->amountOfQuestions['group']) }}</div>
                <div class="bold">{{ $this->test->getTotalScore() }}pt.</div>
            </div>
        </div>
        @empty($this->mode)
            <div class="flex w-full justify-end mt-3 note text-sm space-x-2.5">
                <x-actions.test-delete :uuid="$this->uuid"/>
                <x-actions.test-open-settings :uuid="$this->uuid"/>
                <x-actions.test-open-edit :uuid="$this->uuid"/>
                <x-actions.test-open-preview :uuid="$this->uuid"/>

                <livewire:actions.test-make-pdf :uuid="$this->uuid" :wire:key="'make-pdf-'.$this->uuid"/>
                <livewire:actions.test-duplicate-test :uuid="$this->uuid" :wire:key="'duplicate-test-'.$this->uuid"/>
                <livewire:actions.test-quick-take :uuid="$this->uuid" :wire:key="'quick-take-'.$this->uuid"/>

                @if($this->test->isPublished())
                    <livewire:actions.test-plan-test :uuid="$this->uuid" :wire:key="'plan-test-'.$this->uuid"/>
                @else
                    <livewire:actions.test-make-published :uuid="$this->uuid" :wire:key="'make-published'.$this->uuid"/>
                @endif
            </div>
        @endempty
        <div class="flex w-full" x-show="testDetailBodyVisibility">
            <div class="w-full mx-auto divide-y divide-secondary z-0">
                {{-- Content --}}
                <div class="flex flex-col py-4" style="min-height: 500px">
                    <x-grid class="mt-4">
                        @foreach(range(1, 6) as $value)
                            <x-grid.loading-card :delay="$value"/>
                        @endforeach

                        @foreach($this->test->testQuestions->sortBy('order') as $testQuestion)
                            <x-grid.question-card-detail :testQuestion="$testQuestion"
                                                         :mode="$this->mode ?? 'page'"
                                                         :inTest="$this->testContainsQuestion($testQuestion->question)"
                            />
                        @endforeach
                    </x-grid>
                    <livewire:context-menu.question-card/>
                </div>
            </div>
        </div>
    </div>
    <x-notification/>
    <div id="groupdetail" :style="{'left': groupDetailActive ? '0' : '100%'}" style="@if($this->groupQuestionDetail === null) display:none;@endif">
        <div>
            @if($this->groupQuestionDetail !== null)
                <x-partials.group-question-details :groupQuestion="$this->groupQuestionDetail"
                                                   :context="$this->context"/>
            @endif
        </div>
    </div>
    <x-after-planning-toast/>
</div>
