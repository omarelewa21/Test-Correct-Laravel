@props(['loop' => 1])
<div class="question-button dummy @if($this->type === 'GroupQuestion') group-question-questions @endif"
     x-data="{mode: @entangle('action'), owner: @entangle('owner'), name: @entangle('newQuestionTypeName'), disabledSub: true,
     dummyVisible: false,
        shouldIBeVisible() {
           this.dummyVisible = this.mode === 'add' && this.owner === 'test';
        },
        init() {
            this.$watch('dummyVisible', value => {
                if(!value) return;
                setTimeout(() => {
                    this.$dispatch('scroll-dummy-into-view')
                },300)
            });
        }
     }"
     x-show="dummyVisible"
     x-cloak
     x-effect="shouldIBeVisible()"
     :class="{'question-active': dummyVisible}"
     wire:key="dummy-{{ $loop.$this->owner }}"
>
    <div class="flex items-center cursor-pointer bold py-2 hover:text-primary pl-6 pr-4 question-active hover:bg-primary/5"
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
    @if($this->type === 'GroupQuestion')
        <div class="group-add-new relative hover:bg-primary/5 flex space-x-2.5 py-2 px-6 text-sysbase hover:text-primary cursor-pointer items-center transition-colors"
             selid="add-question-in-new-group-btn"
             :class="{'!text-note hover:!bg-white hover:!text-note !cursor-default': disabledSub}"
             x-on:click="if (!disabledSub) addSubQuestionToNewGroup()"
             x-on:group-question-name-filled.window="disabledSub = false"
             x-on:group-question-name-empty.window="disabledSub = true"
        >
            <x-icon.plus-in-circle/>
            <span class="flex bold">{{ __('cms.Vraag toevoegen')}}</span>
        </div>
    @endif
</div>