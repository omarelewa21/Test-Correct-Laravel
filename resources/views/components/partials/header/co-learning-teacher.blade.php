<header id="header"
        @class([
            'maintenance-header-bg' => $hasActiveMaintenance,
            'deployment-testing-marker' => $isOnDeploymentTesting,
            'h-[var(--header-height)]' => $this->coLearningHasBeenStarted,
            'h-full' => ! $this->coLearningHasBeenStarted,
        ])
        x-data="{
            coLearningSessionStarted: @js($this->coLearningHasBeenStarted),
            collapseHeader: false,
            startCoLearningSession: async function(type = 'OPEN_ONLY', resetProgress){
                result = await $wire.startCoLearningSession(type, resetProgress);
                if(result !== false) {
                    this.coLearningSessionStarted = true;
                }
            }
        }"

        @if(!$this->coLearningHasBeenStarted)
            x-show="!coLearningSessionStarted"
            x-collapse.min.70px.duration.1500ms
        @endif
>
    <div class="py-2.5 px-6 flex h-[var(--header-height)] items-center justify-between">
        <div class="flex items-center space-x-4">
            <x-button.back-round wire:click="redirectBack()" class="bg-white/20 hover:text-white"></x-button.back-round>
            <h6 class="text-white">{{ __('co-learning.co_learning') }}: </h6>
            <h4 class="text-white">{!!  clean($testName) !!}</h4>
        </div>
        <div class="flex">
            @if($this->coLearningHasBeenStarted)
                <div class="text-right text-[14px] mr-4">
                    {{ __('co-learning.questions_being_discussed') }}<br>
                    {{ $discussionTypeTranslation }}
                </div>
                <div class="flex items-center">
                    <x-button.cta :disabled="!$atLastQuestion"
                                  @class(['opacity-40' => !$atLastQuestion])
                                  wire:click.prevent="finishCoLearning"
                    >
                        {{ __('co-learning.complete') }}
                        <x-icon.checkmark class="ml-2"/>
                    </x-button.cta>
                </div>
            @endif
        </div>
    </div>
    @if(!$this->coLearningHasBeenStarted)
        <div id="start-screen-content" class="h-full flex justify-center items-center">
            <div class="flex flex-col mb-[110px]">
                <div class="flex items-center justify-center h-8 mb-4">
                    <h3 class="text-center text-white">Kies je sessie methode</h3>
                </div>
                <div class="grid grid-cols-2 gap-5">

                    <div @class([
                       "co-learning-panel",
                       "co-learning-restart" => $this->coLearningRestart
                       ])
                    >
                        <div>
                            <x-stickers.questions-all/>
                        </div>
                        <div class="flex justify-center items-center mt-2">
                            <h5 class="text-white">{{  \Illuminate\Support\Str::ucfirst(__('co-learning.all_questions')) }}</h5>
                        </div>
                        <div id="text-body" class="space-y-6">
                            <div>{{ __('co-learning.all_questions_text') }}</div>
                            <div class="text-[14px]">{{ __('co-learning.all_questions_note') }}</div>
                        </div>
                        <div>
                            <x-button.cta size="md" wire:click.prevent="startCoLearningSession('ALL', {{ $this->openOnly ? 'true' : 'false' }})">
                                <span>{{ __('co-learning.start') }}</span>
                                <x-icon.arrow/>
                            </x-button.cta>
                        </div>
                        @if($this->coLearningRestart)
                            @if($this->openOnly)
                                <div class="text-center text-[14px]">
                                    {!!  __('co-learning.restart_session') !!}
                                </div>
                            @else
                                <div class="text-center text-[14px]">
                                    {!!  __('co-learning.current_session', [
                                    'index' => $this->questionIndex,
                                    'totalQuestions' => $this->questionCount,
                                    'date' => $this->testTake->updated_at->format('d/m/Y')
                                    ]) !!}
                                </div>
                            @endif
                        @endif
                    </div>
                    <div @class([
                       "co-learning-panel",
                       "co-learning-restart" => $this->coLearningRestart
                       ])
                    >
                        <div>
                            <x-stickers.questions-open-only/>
                        </div>
                        <div class="flex justify-center items-center mt-2">
                            <h5 class="text-white">{{ \Illuminate\Support\Str::ucfirst(__('co-learning.open_questions_only')) }}</h5>
                        </div>
                        <div id="text-body" class="space-y-6">
                            <div>{{ __('co-learning.open_questions_text') }}</div>
                            <div class="text-[14px]">{{ __('co-learning.open_questions_note') }}</div>
                        </div>
                        <div>
                            <x-button.cta size="md" @click.prevent="startCoLearningSession('OPEN_ONLY', {{ !$this->openOnly ? 'true' : 'false' }})">
                                <span>{{ __('co-learning.start') }}</span>
                                <x-icon.arrow/>
                            </x-button.cta>
                        </div>
                        @if($this->coLearningRestart)
                            @if($this->openOnly)
                                <div class="text-center text-[14px]">
                                    {!!  __('co-learning.current_session', [
                                    'index' => $this->questionIndexOpenOnly,
                                    'totalQuestions' => $this->questionCountOpenOnly,
                                    'date' => $this->testTake->updated_at->format('d/m/Y')
                                    ]) !!}
                                </div>
                            @else
                                <div class="text-center text-[14px]">
                                    {!!  __('co-learning.restart_session') !!}
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>

    @endif
</header>