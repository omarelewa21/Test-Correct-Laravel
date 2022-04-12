@props([
    'question',
    'loop',
    'subQuestion' => 'false',
    'testQuestion' => null,
])
<div class="question-button flex items-center cursor-pointer bold py-2 @if($subQuestion === 'false') pl-6 pr-4 @endif @if($question->uuid === $this->testQuestionId) primary active @endif"
     wire:click="showQuestion('{{ $testQuestion ? $testQuestion->uuid : null }}', '{{ $question->uuid }}', {{ $subQuestion }})"
     @click="$dispatch('question-change', {old: '{{ $this->testQuestionId }}', new: '{{ $question->uuid }}' })"
     style="max-width: 300px"
>
    <div class="flex w-full">
        <span class="rounded-full text-sm flex items-center justify-center border-3 relative px-1.5
        @if($question->uuid === $this->testQuestionId) text-white bg-primary border-primary @else bg-white border-sysbase text-sysbase @endif"
              style="min-width: 30px; height: 30px"
        >
            <span class="mt-px question-number">{{ $loop  }}</span>
        </span>
        <div class="flex mt-.5 flex-1">
            <div class="flex flex-col flex-1 pl-2 pr-4">
                <span class="max-w-[167px] truncate" title="{{ $question->title }}">{{ $question->title }}</span>

                <div class="flex note text-sm regular justify-between">
                    <span>{{ __($this->getQuestionNameForDisplay($question)) }}</span>
                    <div class="flex items-center space-x-2">
                        <span class="flex">{{ $question->score }}pt</span>
                        @if($subQuestion === 'false')
                            <div class="flex items-center space-x-1">
                                <x-icon.attachment class="flex"/>
                                <span class="flex">{{ $question->attachments()->count() }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="flex items-start space-x-2.5 mt-1 text-sysbase"
                 x-data="{
                         options:false,
                          toggleOptions(e){
                             e.stopPropagation();
                             this.options=true;
                         },
                         hideOptions(e){
                            e.stopImmediatePropagation();
                            e.preventDefault();
                            e.stopPropagation();
                            this.options = false;
                            return false;
                         }
                     }"
            >
                <div class="flex h-full rounded-md">
                    <x-icon.locked/>
                </div>
                <div class="flex">
                    <button class="px-2 flex rounded-md hover:text-primary transition relative"
                            @click.stop="options = true"
                    >
                        <div x-show="options" @click.stop="options=false" class="fixed inset-0 " style="width: var(--sidebar-width)"></div>
                        <x-icon.options/>
                        <div x-cloak
                             x-show="options"
                             x-ref="optionscontainer"
                             class="absolute flex flex-col -right-5 top-5 bg-white text-sysbase py-2 main-shadow rounded-10 w-72 z-10"
                             @click.outside="options = false"

                             x-transition:enter="transition ease-out origin-top-right duration-200"
                             x-transition:enter-start="opacity-0 transform scale-90"
                             x-transition:enter-end="opacity-100 transform scale-100"
                             x-transition:leave="transition origin-top-right ease-in duration-100"
                             x-transition:leave-start="opacity-100 transform scale-100"
                             x-transition:leave-end="opacity-0 transform scale-90"
                        >
                            <div class="flex items-center space-x-2 py-1 px-4 base hover:text-primary hover:bg-offwhite transition w-full" title="{{ __('cms.Verwijderen') }}">
                                <x-icon.trash/>
                                <span class="text-base bold inherit">{{ __('cms.Verwijderen') }}</span>
                            </div>
                            <div class="flex items-center space-x-2 py-1 px-4 base hover:text-primary hover:bg-offwhite transition w-full"
                                 title="{{ __('cms.Wijzigen') }}"
                                 wire:click="showQuestion('{{ $testQuestion ? $testQuestion->uuid : null }}', '{{ $question->uuid }}', false)"
                                 @click="$dispatch('question-change', {old: '{{ $this->testQuestionId }}', new: '{{ $question->uuid }}' })"
                            >
                                <x-icon.edit/>
                                <span class="text-base bold inherit">{{ __('cms.Wijzigen') }}</span>
                            </div>
                        </div>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
