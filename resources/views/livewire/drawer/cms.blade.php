<div class="drawer flex z-[3]"
     x-data="{collapse: false, backdrop: false}"
     x-init="
        collapse = window.innerWidth < 1000;
        handleBackdrop = () => {
            if(backdrop) {
                $root.dataset.closedWithBackdrop = 'true';
                backdrop = !backdrop
            } else {
                if ($root.dataset.closedWithBackdrop === 'true') {
                    backdrop = true;
                }
            }
        }
"
     :class="{'collapsed': collapse}"
     x-cloak
     wire:ignore.self
     @backdrop="backdrop = !backdrop"
>
    <div id="sidebar-backdrop"
         class="fixed inset-0 transform transition-all"
         x-show="backdrop"
         x-cloak
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
    </div>
    <div id="sidebar-content" class="flex flex-col bg-white">
        <div class="collapse-toggle vertical white z-10 cursor-pointer"
             @click="collapse = !collapse; handleBackdrop()"
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
            <x-sidebar.slide-container class="pt-4 divide-y divide-bluegrey" x-ref="container1">
                <div class="divide-y divide-bluegrey pb-6">
                    @php $loopIndex = 0; @endphp
                    @foreach($this->questionsInTest as $testQuestion)
                        @if($testQuestion->question->type === 'GroupQuestion')
                            <x-sidebar.cms.group-question-container
                                    :testQuestion="$testQuestion"
                                    :question="$testQuestion->question"

                            >
                                @foreach($testQuestion->question->subQuestions as $question)
                                    @php $loopIndex ++; @endphp
                                    <x-sidebar.cms.question-button :testQuestion="$testQuestion"
                                                                   :question="$question"
                                                                   :loop="$loopIndex"
                                                                   :subQuestion="true"
                                    />
                                @endforeach
                                <x-sidebar.cms.dummy-question-button :testQuestion="$testQuestion" :loop="$loopIndex"/>
                            </x-sidebar.cms.group-question-container>
                        @else
                            @php $loopIndex ++; @endphp
                            <x-sidebar.cms.question-button :testQuestion="$testQuestion"
                                                           :question="$testQuestion->question"
                                                           :loop="$loopIndex "
                            />
                        @endif
                    @endforeach
                    <x-sidebar.cms.dummy-question-button :loop="$loopIndex"/>
                </div>
                <div wire:loading class="fixed inset-0" style="width: var(--sidebar-width)"></div>
                <x-button.plus-circle wire:click="addGroup">
                    {{ __('cms.Vraaggroep toevoegen') }}
                </x-button.plus-circle>

                <x-button.plus-circle @click="next($refs.container1);$dispatch('backdrop')"
                >
                    {{__('cms.Vraag toevoegen')}}
                </x-button.plus-circle>

                <span></span>
            </x-sidebar.slide-container>

            <x-sidebar.slide-container x-ref="container2">
                <div class="py-1 px-6 flex">
                    <x-button.text-button class="rotate-svg-180"
                                          @click="prev($refs.container2); $dispatch('backdrop')"
                                          wire:click="$set('groupId', null)"
                    >
                        <x-icon.arrow/>
                        <span>{{ __('cms.Vraag toevoegen') }}</span>
                    </x-button.text-button>
                </div>

                <x-button.plus-circle @click="showNewQuestion($refs.container2)">
                    {{ __( 'cms.Nieuwe vraag creeren' ) }}
                </x-button.plus-circle>
                <x-button.plus-circle @click="showQuestionBank()">
                    {{ __( 'cms.Bestaande vraag toevoegen' ) }}
                </x-button.plus-circle>

            </x-sidebar.slide-container>
            <x-sidebar.slide-container x-ref="questionbank">
                <div class="py-1 px-6 flex">
                    <x-button.text-button class="rotate-svg-180"
                                          @click="prev($refs.container2);hideQuestionBank($refs.container2)"
                                          wire:click="$set('groupId', null)"
                    >
                        <x-icon.arrow/>
                        <span>{{ __('cms.Bestaande vraag toevoegen') }}</span>
                    </x-button.text-button>

                    <div class="flex ml-auto items-center">
                        <div x-data="{active: 2}"
                             class="text-toggle inline-flex border border-secondary bg-offwhite relative rounded-lg h-10 ">
                            <span class="px-4 py-2 bold note cursor-default"
                                  :class="{'primary': active === 1}">{{ __('cms.Toetsenbank') }}</span>
                            <span @click="active = 2" class="px-4 py-2 bold"
                                  :class="{'primary': active === 2}">{{ __('cms.Vragenbank') }}</span>

                            <span class="active-border absolute -inset-px border-2 border-primary rounded-lg transition-all"
                                  :style="active === 1 ? 'left:0' : 'left:'+ $root.offsetWidth/2 +'px' "
                            ></span>
                        </div>
                    </div>

                </div>

                <livewire:teacher.question-bank/>

            </x-sidebar.slide-container>
            <x-sidebar.slide-container x-ref="newquestion">
                <div class="py-1 px-6">
                    <x-button.text-button class="rotate-svg-180"
                                          @click="prev($refs.newquestion)"
                                          wire:click="$set('groupId', null)"
                    >
                        <x-icon.arrow/>
                        <span>{{ __('cms.choose-question-type') }}</span>
                    </x-button.text-button>
                </div>

                <x-sidebar.question-types/>
            </x-sidebar.slide-container>

        </div>
        <span class="invisible"></span>
    </div>
</div>
