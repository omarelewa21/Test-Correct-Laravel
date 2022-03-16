<div class="drawer flex z-[3]"
     x-data="{collapse: false}"
     x-init="collapse = window.innerWidth < 1000"
     :class="{'collapsed': collapse}"
     x-cloak
     wire:ignore.self
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
             x-ref="questionEditorSidebar"
             wire:ignore.self
        >
            <x-sidebar.slide-container class="divide-y divide-bluegrey" x-ref="container1">
                <div class="divide-y divide-bluegrey">
                    @php $loopIndex = 0; @endphp
                    @foreach($this->questionsInTest as $testQuestion)
                        @if($testQuestion->question->type === 'GroupQuestion')
                            <x-sidebar.cms.group-question-container :testQuestion="$testQuestion" :question="$testQuestion->question">
                                @foreach($testQuestion->question->subQuestions as $question)
                                    @php $loopIndex ++; @endphp
                                    <x-sidebar.cms.question-button :testQuestion="$testQuestion"
                                                                   :question="$question"
                                                                   :loop="$loopIndex"
                                                                   :subQuestion="true"
                                    />
                                @endforeach

                            </x-sidebar.cms.group-question-container>
                        @else
                            @php $loopIndex ++; @endphp
                            <x-sidebar.cms.question-button :testQuestion="$testQuestion"
                                                           :question="$testQuestion->question"
                                                           :loop="$loopIndex "
                            />
                        @endif
                    @endforeach
                </div>

                <div class="flex px-6 py-2.5 space-x-2.5 hover:text-primary">
                    <x-icon.plus-in-circle/>
                    <button class="bold">{{__('cms.Vraaggroep toevoegen')}}</button>
                </div>

                <div class="flex px-6 py-2.5 space-x-2.5 hover:text-primary"
                     @click="next($refs.container1)"
                >
                    <x-icon.plus-in-circle/>
                    <button class="bold">{{__('cms.Vraag toevoegen')}}</button>
                </div>
                <span></span>
            </x-sidebar.slide-container>

            <x-sidebar.slide-container x-ref="container2">
                <div class="py-1 px-6">
                    <x-button.text-button class="rotate-svg-180"
                                          @click="prev($refs.container2)"
                                          wire:click="$set('groupId', null)"
                    >
                        <x-icon.arrow/>
                        <span>{{ __('cms.choose-question-type') }}</span>
                    </x-button.text-button>
                </div>

                <x-sidebar.question-types/>

            </x-sidebar.slide-container>
        </div>
    </div>
</div>
