<div cms-drawer
     class="drawer flex z-[20]"
     selid="question-drawer"
     x-init="
        collapse = window.innerWidth < 1000;
        if (emptyStateActive) {
            $store.cms.emptyState = true
            backdrop = true;
        }
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
        $watch('emptyStateActive', (value) => {
            backdrop = value
            $store.cms.emptyState = value
        })
        handleLoading = () => {
            loadingOverlay = $store.cms.loading;
        }
        handleSliderClick = (event) => {
            if (!event.target.classList.contains('slider-option')) {
                return
            }
            document.querySelectorAll('.option-menu-active').forEach((el) => $dispatch(el.getAttribute('context')+'-context-menu-close') );
            $nextTick(() => showBank = event.target.firstElementChild.dataset.id);
        }


     "
     x-data="{loadingOverlay: false, collapse: false, backdrop: false, emptyStateActive: @entangle('emptyStateActive'), showBank: @js($sliderButtonSelected)}"
     x-cloak
     x-effect="handleLoading();"
     :class="{'collapsed': collapse}"
     @backdrop="backdrop = !backdrop"
     @processing-end.window="$store.cms.processing = false;"
     @filepond-start.window="loadingOverlay = true;"
     @filepond-finished.window="loadingOverlay = false;"
     @first-question-of-test-added.window="$wire.showFirstQuestionOfTest(); emptyStateActive = false; $nextTick(() => backdrop = true)"
     @hide-backdrop-if-active.window="if(backdrop) backdrop = false"
     @scroll="$store.cms.scrollPos = $el.scrollTop;"
     @closed-with-backdrop.window="$root.dataset.closedWithBackdrop = $event.detail"
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
             selid="sidebar-question-list"
             x-data="questionEditorSidebar"
             x-ref="questionEditorSidebar"
             wire:ignore.self
             :class="{'!invisible': resizing}"
             @resize.window="handleResizing()"
        >
            <x-sidebar.slide-container class="divide-y divide-bluegrey"
                                       x-ref="home"
                                       @mouseenter="handleVerticalScroll($el);"
                                       @continue-to-new-slide.window="$wire.removeDummy();showAddQuestionSlide(false)"
                                       @continue-to-add-group.window="addGroup(false)"
                                       @scroll-dummy-into-view.window="scrollActiveQuestionIntoView()"
            >
                <div wire:sortable="updateTestItemsOrder"
                     class="sortable-drawer divide-y divide-bluegrey pb-6 pt-4" {{ $emptyStateActive ? 'hidden' : '' }} >
                    @php $loopIndex = 0; @endphp
                    @foreach($this->questionsInTest as $testQuestion)
                        @if($testQuestion->question->type === 'GroupQuestion')
                            <x-sidebar.cms.group-question-container
                                    :question="$testQuestion->question"
                                    :testQuestion="$testQuestion"
                                    :double="$this->duplicateQuestions->contains($testQuestion->question->id)"
                            >
                                @foreach($testQuestion->question->subQuestions as $question)
                                    @php $loopIndex ++; @endphp
                                    <x-sidebar.cms.question-button :testQuestion="$testQuestion"
                                                                   :question="$question"
                                                                   :loop="$loopIndex"
                                                                   :subQuestion="true"
                                                                   :activeTestQuestion="$this->testQuestionId"
                                                                   :activeGQQ="$this->groupQuestionQuestionId"
                                                                   :double="$this->duplicateQuestions->contains($question->id) || $this->duplicateQuestions->contains($testQuestion->question->id)"
                                    />
                                @endforeach
                                <x-sidebar.cms.dummy-group-question-button :testQuestionUuid="$testQuestion->uuid"
                                                                           :loop="$loopIndex"/>
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

                <x-button.plus-circle @click.stop="addGroup()" selid="add-question-group-btn">
                    {{ __('cms.Vraaggroep toevoegen') }}
                </x-button.plus-circle>

                <x-button.plus-circle @click.stop="showAddQuestionSlide()" selid="add-question-btn"
                >
                    {{__('cms.Vraag toevoegen')}}
                </x-button.plus-circle>
                <span id="c1-bottom-spacer"></span>
            </x-sidebar.slide-container>

            <x-sidebar.slide-container class="divide-y divide-bluegrey" x-ref="type"
                                       @mouseenter="handleVerticalScroll($el);">
                <div class="py-2 px-5 flex">
                    <div class="flex items-center space-x-2.5">
                        <x-button.back-round @click="backToQuestionOverview($refs.type);"
                                             wire:click="$set('groupId', null)"
                        />
                        <span class="bold text-lg">{{ __('cms.Vraag toevoegen') }}</span>
                    </div>
                </div>

                <x-button.plus-circle class="py-4" @click="showNewQuestion($refs.type)"
                                      wire:loading.class="pointer-events-none" selid="create-new-question-btn">
                    {{ __( 'cms.Nieuwe creeren' ) }}
                    <x-slot name="subtext">{{ __('cms.Stel een nieuwe vraag op') }}</x-slot>
                </x-button.plus-circle>

                <x-button.plus-circle class="py-4" @click="showQuestionBank()" selid="add-existing-question-btn">
                    {{ __( 'cms.Bestaande toevoegen' ) }}
                    <x-slot name="subtext">{{ __('cms.Verken en kies uit vragenbank') }}</x-slot>
                </x-button.plus-circle>
                <span></span>
            </x-sidebar.slide-container>
            <x-sidebar.slide-container x-ref="questionbank" @mouseenter="handleVerticalScroll($el);"
                                       x-on:click="handleSliderClick($event)"
                                       style="max-height: calc(100vh - var(--header-height))"
            >
                <div class="py-2 px-6 flex w-full bg-white z-10">
                    <div class="flex items-center space-x-2.5">
                        <x-button.back-round @click="hideQuestionBank();" selid="question-bank-back-btn"/>
                        <span class="bold text-lg cursor-default">{{ __('cms.Bestaande vraag toevoegen') }}</span>
                    </div>

                    <div class="flex ml-auto items-center space-x-2.5 relative">
                        <x-button.cta @click="hideQuestionBank();" selid="close-question-bank-btn">
                            <span>{{ __('drawing-modal.Sluiten') }}</span>
                        </x-button.cta>

                        <x-button.slider wire:model.debounce.300ms="sliderButtonSelected"
                                         button-width="135px"
                                         :disabled="$sliderButtonDisabled"
                                         :options="$sliderButtonOptions"
                        />
                    </div>
                </div>

                <div class="flex flex-1 w-full" wire:key="selected-tab-{{ $sliderButtonSelected }}">
                    <div class="w-full flex flex-1" x-cloak x-show="showBank === 'tests'">
                        <livewire:teacher.cms-tests-overview :cmsTestUuid="$this->testId"/>
                    </div>

                    <div class="w-full flex flex-1" x-cloak x-show="showBank === 'questions'">
                        <livewire:teacher.question-bank :testId="$this->testId" mode="cms"/>
                    </div>
                </div>
            </x-sidebar.slide-container>
            <x-sidebar.slide-container x-ref="newquestion" @mouseenter="handleVerticalScroll($el);">
                <div class="py-2 px-5">
                    <div class="flex items-center space-x-2.5">
                        <x-button.back-round @click="home(); $store.questionBank.inGroup = false;"
                                             wire:click="$set('groupId', null)"
                        />
                        <span class="bold text-lg">{{ __('cms.choose-question-type') }}</span>
                    </div>
                </div>

                <x-sidebar.cms.question-types/>
            </x-sidebar.slide-container>
        </div>
        <span class="invisible"></span>
    </div>
</div>