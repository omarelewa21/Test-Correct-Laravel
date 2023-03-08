@extends('components.partials.header.collapsable')

@section('title')
    <h6 class="text-white">@lang($this->headerCollapsed ? 'co-learning.co_learning' : 'start_co_learning_session')
        : </h6>
    <h4 class="text-white">{!!  clean($testName) !!}</h4>
@endsection

@section('subtitle')
    @lang('co-learning.choose_method_subtitle')
@endsection
@section('collapsedLeft')
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
            <x-icon.checkmark class="ml-2" />
        </x-button.cta>
    </div>
@endsection

@section('panels')
    <x-partials.header.panel @class([
                "co-learning-restart" => $this->coLearningRestart,
                "co-learning-previous-discussion-type" => !$this->openOnly,
                ])>
        <x-slot:sticker>
            <x-stickers.questions-all />
        </x-slot:sticker>
        <x-slot:title>{{  str(__('co-learning.all_questions'))->ucfirst() }}</x-slot:title>
        <x-slot:subtitle>
            <div>{{ __('co-learning.all_questions_text') }}</div>
            <div class="text-[14px]">{{ __('co-learning.all_questions_note') }}</div>
        </x-slot:subtitle>
        <x-slot:button>
            <x-button.cta size="md"
                          wire:click.prevent="startCoLearningSession('ALL', {{ $this->openOnly ? 'true' : 'false' }})">
                <span>
                    @lang($this->coLearningRestart && !$this->openOnly ? 'auth.continue' : 'co-learning.start')
                </span>
                <x-icon.arrow />
            </x-button.cta>
        </x-slot:button>
        <x-slot:additionalInfo>
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
        </x-slot:additionalInfo>
    </x-partials.header.panel>

    <x-partials.header.panel @class([
                        "co-learning-restart" => $this->coLearningRestart,
                        "co-learning-previous-discussion-type" => $this->openOnly,
                        ])>
        <x-slot:sticker>
            <x-stickers.questions-open-only />
        </x-slot:sticker>
        <x-slot:title>{{ str(__('co-learning.open_questions_only'))->ucfirst() }}</x-slot:title>
        <x-slot:subtitle>
            <div>{{ __('co-learning.open_questions_text') }}</div>
            <div class="text-[14px]">{{ __('co-learning.open_questions_note') }}</div>
        </x-slot:subtitle>
        <x-slot:button>
            <x-button.cta size="md"
                          @click.prevent="handleHeaderCollapse({discussionType:'OPEN_ONLY',resetProgress:{{ !$this->openOnly ? 'true' : 'false' }} })">
                <span>
                    @lang($this->coLearningRestart && $this->openOnly ? 'auth.continue' : 'co-learning.start')
                </span>
                <x-icon.arrow />
            </x-button.cta>
        </x-slot:button>
        <x-slot:additionalInfo>
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
        </x-slot:additionalInfo>
    </x-partials.header.panel>
@endsection