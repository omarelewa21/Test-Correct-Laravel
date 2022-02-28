<div class="drawer flex z-[1]"
     x-data="{collapse: false}"
     x-init="collapse = window.innerWidth < 1000"
     :class="{'collapsed': collapse}"
>
    <div id="sidebar-content" class="flex flex-col">
        <div class="collapse-toggle vertical white z-10 cursor-pointer"
             @click="collapse = !collapse"
        >
            <button class="relative"
                    :class="{'rotate-svg-180 -left-px': !collapse}"
            >
                <x-icon.chevron class="-top-px relative"/>
            </button>
        </div>

        <div id="sidebar-carousel-container"
             x-data="questionEditorSidebar"
        >
            <x-sidebar.slide-container class="divide-y divide-bluegrey" x-ref="container1">
                <div>
                    @foreach($this->currentTestQuestions as $question)
                        <div>
                        <span class="flex @if($this->testQuestionId === $question['uuid']) primary @endif"
                              wire:click="showQuestion('{{ $question['uuid'] }}')"
                              @click="$dispatch('question-change', {old: '{{ $this->testQuestionId }}', new: '{{ $question['uuid'] }}' })"
                        >
                            <span class="w-8 h-8 rounded-full text-white text-sm flex items-center justify-center
                            @if($this->testQuestionId === $question['uuid']) bg-primary @else bg-sysbase @endif">
                                <span>{{ $question['order'] == 0 ? '1' : $loop->iteration}}</span>
                            </span>

                            <span>{!! $question['question'] !!}</span>
                        </span>
                        </div>
                    @endforeach
                </div>

                <div class="flex px-6 py-2.5 space-x-2.5 hover:text-primary">
                    <x-icon.plus-in-circle/>
                    <button class="bold">Vraaggroep toevoegen</button>
                </div>

                <div class="flex px-6 py-2.5 space-x-2.5 hover:text-primary"
                     @click="next($refs.container1)"
                >
                    <x-icon.plus-in-circle/>
                    <button class="bold">Vraag toevoegen</button>
                </div>
                <span></span>
            </x-sidebar.slide-container>

            <x-sidebar.slide-container x-ref="container2">
                <div class="py-1 px-6">
                    <x-button.text-button class="rotate-svg-180"
                                          @click="prev($refs.container2)">
                        <x-icon.arrow/>
                        <span>{{ __('cms.choose-question-type') }}</span>
                    </x-button.text-button>
                </div>

                <x-sidebar.question-types/>

            </x-sidebar.slide-container>
        </div>
    </div>
</div>