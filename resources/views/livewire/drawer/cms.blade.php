<div class="drawer flex z-[20]"
     x-init="
        collapse = window.innerWidth < 1000;
        if (emptyStateActive) backdrop = true;
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
        dispatchBackdrop = () => {
            if(!emptyStateActive) $dispatch('backdrop');
        }
        $watch('emptyStateActive', (value) => backdrop = value)
        handleLoading = () => {
            loadingOverlay = $store.cms.loading;
        }


     "
     x-data="{loadingOverlay: false, collapse: false, backdrop: false, emptyStateActive: @entangle('emptyStateActive')}"
     x-cloak
     x-effect="handleLoading(); $el.scrollTop = $store.cms.scrollPos;"
     :class="{'collapsed': collapse}"
     @backdrop="backdrop = !backdrop"
     @processing-end.window="$store.cms.processing = false;"
     @filepond-start.window="loadingOverlay = true;"
     @filepond-finished.window="loadingOverlay = false;"
     @first-question-of-test-added.window="$wire.showFirstQuestionOfTest(); emptyStateActive = false; $nextTick(() => backdrop = true)"
     wire:ignore.self
     wire:init="handleCmsInit()"
>
    <div id="sidebar-backdrop"
         class="fixed inset-y-0 right-0 transform transition-all"
         style="left: var(--sidebar-width)"
         x-show="backdrop"
         x-cloak
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        <div class="absolute inset-0">
            <div x-show="emptyStateActive"
                 x-cloak
                 class="empty-state-popover py-4 px-6">
                {{--                <div class="absolute right-2 top-1 cursor-pointer" >--}}
                {{--                    <x-icon.close-small/>--}}
                {{--                </div>--}}
                <span class="regular text-base">{{ __('cms.begin_with_making_a_question') }}</span>
            </div>
        </div>
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
            <x-sidebar.slide-container class="pt-4 divide-y divide-bluegrey"
                                       x-ref="container1"
                                       @mouseenter="handleVerticalScroll($el);"
                                       @continue-to-new-slide.window="$store.cms.processing = true;$wire.removeDummy();showAddQuestionSlide(false)"
                                       @continue-to-add-group.window="addGroup(false)"
            >
                <div wire:sortable="updateTestItemsOrder" wire:sortable-group="updateGroupItemsOrder" class="sortable-drawer divide-y divide-bluegrey pb-6" {{ $emptyStateActive ? 'hidden' : '' }} >
                    @php $loopIndex = 0; @endphp
                    @foreach($this->questionsInTest as $testQuestion)
                        @if($testQuestion->question->type === 'GroupQuestion')
                            <x-sidebar.cms.group-question-container
                                    :question="$testQuestion->question"
                                    :testQuestion="$testQuestion"

                            >
                                @foreach($testQuestion->question->subQuestions as $question)
                                    @php $loopIndex ++; @endphp
                                    <x-sidebar.cms.question-button :testQuestion="$testQuestion"
                                                                   :question="$question"
                                                                   :loop="$loopIndex"
                                                                   :subQuestion="true"
                                                                   :activeTestQuestion="$this->testQuestionId"
                                                                   :activeGQQ="$this->groupQuestionQuestionId"
                                                                   :double="$this->duplicateQuestions->contains($testQuestion->question_id)"
                                    />
                                @endforeach
                                <x-sidebar.cms.dummy-group-question-button :testQuestionUuid="$testQuestion->uuid" :loop="$loopIndex"/>
                            </x-sidebar.cms.group-question-container>
                        @else
                            @php $loopIndex ++; @endphp
                            <x-sidebar.cms.question-button :testQuestion="$testQuestion"
                                                           :question="$testQuestion->question"
                                                           :loop="$loopIndex"
                                                           :subQuestion="false"
                                                           :activeTestQuestion="$this->testQuestionId"
                                                           :activeGQQ="$this->groupQuestionQuestionId"
                                                           :double="$this->duplicateQuestions->contains($testQuestion->question_id)"
                            />
                        @endif
                    @endforeach
                    <x-sidebar.cms.dummy-question-button :loop="$loopIndex"/>
                </div>
                
                <div wire:loading
                     wire:loading.class.remove="hidden"
                     wire:loading.attr.remove="hidden"
                     hidden
                     class="fixed hidden inset-0" style="width: var(--sidebar-width)"></div>
                <div x-show="loadingOverlay"
                     class="fixed inset-0 bg-white opacity-20"
                     style="width: var(--sidebar-width)"></div>

                <x-button.plus-circle @click="addGroup()">
                    {{ __('cms.Vraaggroep toevoegen') }}
                </x-button.plus-circle>

                <x-button.plus-circle @click="showAddQuestionSlide();dispatchBackdrop()"
                >
                    {{__('cms.Vraag toevoegen')}}
                </x-button.plus-circle>
                <span></span>
            </x-sidebar.slide-container>

            <x-sidebar.slide-container class="divide-y divide-bluegrey" x-ref="container2" @mouseenter="handleVerticalScroll($el);">
                <div class="py-1 px-6 flex">
                    <x-button.text-button class="rotate-svg-180"
                                          @click="backToQuestionOverview($refs.container2);dispatchBackdrop()"
                                          wire:click="$set('groupId', null)"
                    >
                        <x-icon.arrow/>
                        <span>{{ __('cms.Vraag toevoegen') }}</span>
                    </x-button.text-button>
                </div>

                <x-button.plus-circle class="py-4" @click="showNewQuestion($refs.container2)">
                    {{ __( 'cms.Nieuwe creeren' ) }}
                    <x-slot name="subtext">{{ __('cms.Stel een nieuwe vraag op') }}</x-slot>
                </x-button.plus-circle>

                <x-button.plus-circle class="py-4" @click="showQuestionBank()">
                    {{ __( 'cms.Bestaande toevoegen' ) }}
                    <x-slot name="subtext">{{ __('cms.Verken en kies uit vragenbank') }}</x-slot>
                </x-button.plus-circle>

{{--                <div class="flex px-6 py-2.5 space-x-2.5 note cursor-default">--}}
{{--                    <x-icon.plus-in-circle/>--}}
{{--                    <div class="flex flex-col ">--}}
{{--                        <button class="bold mt-px text-left cursor-default">{{ __( 'cms.Bestaande toevoegen' ) }}</button>--}}
{{--                        <span class="text-sm note regular">{{ __('cms.Verken en kies uit vragenbank') }}</span>--}}
{{--                    </div>--}}
{{--                </div>--}}

                <span></span>
            </x-sidebar.slide-container>
            <x-sidebar.slide-container x-ref="questionbank" @mouseenter="handleVerticalScroll($el);">
                <div class="py-1 px-6 flex">
                    <x-button.text-button class="rotate-svg-180"
                                          @click="prev($refs.container2);hideQuestionBank($refs.container2); $store.questionBank.inGroup = false;"
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
            <x-sidebar.slide-container x-ref="newquestion" @mouseenter="handleVerticalScroll($el);">
                <div class="py-1 px-6">
                    <x-button.text-button class="rotate-svg-180"
                                          @click="prev($refs.newquestion); $store.questionBank.inGroup = false;"
                                          wire:click="$set('groupId', null)"
                    >
                        <x-icon.arrow/>
                        <span>{{ __('cms.choose-question-type') }}</span>
                    </x-button.text-button>
                </div>

                <x-sidebar.cms.question-types/>
            </x-sidebar.slide-container>

        </div>
        <span class="invisible"></span>
    </div>


    <style>

        .reorder{
            cursor:move;
        }

        .draggable-container--over  .draggable-mirror:before  {
            content: none !important;
        }

        .draggable-mirror:before,
        .draggable-container--over .draggable-group .draggable-mirror:before,
        .draggable-not-droppable{
            content: url('data:image/svg+xml,%3Csvg width="4" height="14" xmlns="http://www.w3.org/2000/svg"%3E %3Cg class="fill-current" fill-rule="evenodd"%3E %3Cpath d="M1.615 0h.77A1.5 1.5 0 013.88 1.61l-.45 6.06a1.436 1.436 0 01-2.863 0L.12 1.61A1.5 1.5 0 011.615 0z"/%3E %3Ccircle cx="2" cy="12" r="2"/%3E %3C/g%3E %3C/svg%3E') !important;
            position: absolute;
            top: -17px;
            right: -17px;
            background: rgb(247,225,223);
            width: 25px;
            height: 25px;
            border-radius: 50%;
            text-align: center;
        }
        .draggable-mirror {
            z-index: 1000;
        }

    </style>
</div>