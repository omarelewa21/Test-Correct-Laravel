@props(['loop' => 1, 'testQuestionUuid' => ''])
<div class="question-button group-dummy pl-6 pr-4"
     x-data="{mode: @entangle('action'), owner: @entangle('owner'), name: @entangle('newQuestionTypeName'), groupId: '{{ $testQuestionUuid }}',
        groupDummyVisible: false,
        shouldIBeVisible() {
            this.groupDummyVisible = this.mode === 'add' && this.owner === 'group' && this.groupId === '{{ $this->testQuestionId }}';
        },
        init() {
            this.$watch('groupDummyVisible', (value) => {
                if(!value) return;
                setTimeout(() => {
                    this.$dispatch('scroll-dummy-into-view')
                },300)
                this.$nextTick(() => this.$el.closest('.group-question-questions')?.dispatchEvent(new CustomEvent('fix-expand-height')) )
            });
        }
     }"
     x-show="groupDummyVisible"
     x-cloak
     x-effect="shouldIBeVisible()"
     :class="{'question-active': groupDummyVisible}"
     wire:key="dummy-{{ $loop.$testQuestionUuid.$this->testQuestionId }}"
>
    <div class="flex items-center cursor-pointer bold py-2 hover:text-primary question-active"
         style="max-width: 300px"
    >
        <div class="flex w-full">
        <span class="rounded-full text-sm flex items-center justify-center border-3 relative px-1.5 text-white bg-midgrey border-mid-grey"
              style="min-width: 30px; height: 30px"
        >
            <span class="mt-px question-number italic">{{ $loop+1  }}</span>
        </span>
            <div class="flex mt-.5 flex-1">
                <div class="flex flex-col flex-1 pl-2 pr-4">
                <span class="truncate italic" style="max-width: 140px;"
                      title="">{{ __('question.no_question_text') }}</span>

                    <div class="flex note text-sm regular justify-between">
                        <span x-text="name"></span>
                        <div class="flex items-center space-x-2"></div>
                    </div>
                </div>
                <div class="flex items-start space-x-2.5 mt-1 text-sysbase hover:text-primary" wire:click="removeDummy">
                    <x-icon.trash/>
                </div>
            </div>
        </div>
    </div>
</div>