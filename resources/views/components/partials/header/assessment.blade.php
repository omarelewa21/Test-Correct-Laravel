@extends('components.partials.header.collapsable')

@section('title')
    <h6 class="text-white">@lang('assessment.Start nakijken'): </h6>
    <h4 class="text-white">{!!  clean($testName) !!}</h4>
@endsection

@section('subtitle')
    @lang('assessment.Kies je nakijkmethode')
@endsection

@section('collapsedLeft')
    <div>
        Hier navigatie
    </div>
@endsection
@section('panels')
    <x-partials.header.panel>
        <x-slot:sticker>
            <x-stickers.questions-all />
        </x-slot:sticker>
        <x-slot:title>{{  str(__('co-learning.all_questions'))->ucfirst() }}</x-slot:title>
        <x-slot:subtitle>
            <div>{{ __('assessnent.all_questions_text') }}</div>
        </x-slot:subtitle>
        <x-slot:button>
            <x-button.cta size="md">
                <span>{{ __('co-learning.start') }}</span>
                <x-icon.arrow />
            </x-button.cta>
        </x-slot:button>
    </x-partials.header.panel>

    <x-partials.header.panel>
        <x-slot:sticker>
            <x-stickers.questions-open-only />
        </x-slot:sticker>
        <x-slot:title>{{ str(__('co-learning.open_questions_only'))->ucfirst() }}</x-slot:title>
        <x-slot:subtitle>{{ __('assessment.open_questions_text') }}</x-slot:subtitle>
        <x-slot:button>
            <x-button.cta size="md"
                          @click.prevent="handleHeaderCollapse(['OPEN_ONLY', true])"
            >
                <span>{{ __('co-learning.start') }}</span>
                <x-icon.arrow />
            </x-button.cta>
        </x-slot:button>
    </x-partials.header.panel>
@endsection()

@section('additionalInfo')
    <div class="flex flex-col w-3/4 self-center divide-white divide-y border-t border-b border-white mt-6">

        <div class="flex py-2 px-4 items-center justify-between">
            <span>@lang('assessment.Alles wordt tussentijds opgeslagen')</span>
            <div class="flex w-[22px] h-[22px] items-center justify-center rounded-full border border-white"
                 title="@lang('assessment.continuously_saved_tooltip')"
            >
                <x-icon.questionmark-small class="inline-flex" />
            </div>
        </div>
        <div class="flex py-2 px-4 items-center justify-between">
            <span>@lang('assessment.Gesloten vragen worden automatisch nagekeken')</span>
            <div class="flex w-[22px] h-[22px] items-center justify-center rounded-full border border-white"
                 title="@lang('assessment.closed_question_checked_tooltip')"
            >
                <x-icon.questionmark-small class="inline-flex" />
            </div>
        </div>
        <div class="flex py-2 px-4 items-center justify-between">
            <span>@lang('assessment.Score wordt overgenomen uit CO-Learning')</span>
            <div class="flex w-[22px] h-[22px] items-center justify-center rounded-full border border-white"
                 title="@lang('assessment.colearning_score_tooltip')"
            >
                <x-icon.questionmark-small class="inline-flex" />
            </div>
        </div>
        <div class="flex py-1.5 px-4 items-center justify-between">
            <span>@lang('assessment.Antwoorden zonder discrepanties overslaan')</span>

            <div class="flex items-center gap-4">
                <x-input.toggle />
                <div class="flex w-[22px] h-[22px] items-center justify-center rounded-full border border-white"
                     title="@lang('assessment.discrepancies_toggle_tooltip')"
                >
                    <x-icon.questionmark-small class="inline-flex" />
                </div>
            </div>
        </div>

    </div>
@endsection
