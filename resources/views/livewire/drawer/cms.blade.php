<div class="drawer flex z-[3] overflow-hidden"
     x-data="{collapse: false}"
     x-init="collapse = window.innerWidth < 1000"
     :class="{'collapsed': collapse}"
     x-cloak
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
                <div class="divide-y divide-bluegrey">
                    @foreach($this->currentTestQuestions as $question)
                        @if($question->type === 'GroupQuestion')
                            <x-sidebar.cms.group-question-container :question="$question">

                                    @foreach($question->subQuestions as $question)
                                        <x-sidebar.cms.question-button :question="$question" :loop="$loop" :subQuestion="true"/>
                                    @endforeach

                            </x-sidebar.cms.group-question-container>
                        @else

                            <x-sidebar.cms.question-button :question="$question" :loop="$loop"/>

                        @endif
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

                    <x-button.text-button class="rotate-svg-180"
                                          @click="next($refs.container2)">
                        <span>Volgende</span>
                        <x-icon.arrow/>
                    </x-button.text-button>
                </div>

                <x-sidebar.question-types/>

            </x-sidebar.slide-container>

            <x-sidebar.slide-container x-ref="container3">
                <div class="py-1 px-6">
                    <x-button.text-button class="rotate-svg-180"
                                          @click="prev($refs.container3)">
                        <x-icon.arrow/>
                        <span>{{ __('cms.choose-question-type') }}</span>
                    </x-button.text-button>
                </div>

                <div class="h-20 bg-allred"></div>
                <div class="h-20 bg-allred"></div>
                <div class="h-20 bg-allred"></div>
                <div class="h-20 bg-allred"></div>

            </x-sidebar.slide-container>
        </div>
    </div>
</div>