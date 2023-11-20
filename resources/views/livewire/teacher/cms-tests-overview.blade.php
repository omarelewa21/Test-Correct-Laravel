@extends('livewire.teacher.tests-overview-layout')

@section('container')
    <div id="testbank"
         x-data="{
        openTab: $wire.entangle('openTab'),
        testQuestionSlide: null,
        overviewBodyVisibility: true,
        maxHeight: 'calc(100vh - var(--header-height))'
     }"
         x-init="
         $watch('showBank', value => {
            if (value === 'tests') {
                $wire.loadSharedFilters();
            }
        });
        testQuestionSlide = $el.querySelector('#test-question-slide')
        showQuestionsOfTest = async (testUuid) => $wire.set('testUuid', testUuid);
        slideOver = () => {
            testQuestionSlide.style.left = 0;
            $el.closest('.drawer').scrollTo({top: 0, behavior: 'smooth'});
            $el.scrollTo({top: 0, behavior: 'smooth'});
            maxHeight = testQuestionSlide.offsetHeight + 'px';
            $nextTick(() => {
                setTimeout(() => {
                    overviewBodyVisibility = false;
                    handleVerticalScroll($el.closest('.slide-container'));
                }, 250);
            })
        };
       closeTestSlide = () => {
            if (!overviewBodyVisibility) {
                overviewBodyVisibility = true;
                maxHeight = 'calc(100vh - var(--header-height))';
                testQuestionSlide.style.left = '100%';
                $nextTick(() => {
                    $wire.set('testUuid', null)
                    setTimeout(() => {
                        handleVerticalScroll($el.closest('.slide-container'));
                    }, 250);
                })
            }

        }

     "
         class="flex flex-col w-full min-h-full bg-lightGrey border-t border-secondary overflow-auto overflow-x-hidden relative"
         x-on:test-questions-ready.window="slideOver()"
         x-bind:style="`max-height: ${maxHeight}`"
    >
@endsection
@section('cms-js-properties')
    x-show="overviewBodyVisibility"
    x-cloak
@endsection

@section('detailSlide')
    <div id="test-question-slide" wire:ignore.self>
        <div class=" mx-auto">
            @if($this->testUuid)
                <livewire:teacher.cms-test-detail :uuid="$this->testUuid" :cmsTestUuid="$this->cmsTestUuid"/>
            @endif
        </div>
    </div>
@endsection

@section('clear-filters-button')
    <x-button.text class="ml-auto text-base"
                          size="sm"
                          wire:click="clearFilters(true)"
                          x-on:click="clearFilterPillsFromElement($refs.questionbank);"
                          :disabled="!$this->hasActiveFilters()"
    >
        <span class="min-w-max">{{ __('teacher.Filters wissen') }}</span>
        <x-icon.close-small/>
    </x-button.text>
@endsection