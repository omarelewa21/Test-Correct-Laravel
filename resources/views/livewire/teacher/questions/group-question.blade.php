@extends('livewire.teacher.questions.cms-layout')
@section('question-cms-group-question')
    <x-input.group class="text-base" label="{{ __('cms.naam vraaggroep') }}">

        <input type="text" wire:model="question.name"
               class="form-input w-full text-left @error('question.name') border border-allred @enderror"/>
    </x-input.group>


    <x-input.group
            class="my-5 text-base"
            label="{{ __('cms.type vraaggroep') }}"
            x-data="{
                value: window.Livewire.find(document.getElementById('cms').getAttribute('wire:id')).entangle('question.groupquestion_type'),
                select: function(option) {
                    this.value = option;
                },
                selected: function(option){
                    return option === this.value;
                },
            }"
    >
        <div x-show="selected('carousel')" x-transition class="mt-3">
            <div class="flex relative -left-4">
                <x-input.score wire:model.defer="question.number_of_subquestions" label="Aantal vragen"/>
            </div>
        </div>
        <div class="flex space-x-2 ">
            <button class="group-type"
                    :class="selected('standard') ? 'active' : ''"
                    @click="select('standard')"
            >
                <div class="flex">
                    <x-stickers.group-classic/>
                </div>

                <div x-show="selected('standard')">
                    <x-icon.checkmark-circle/>
                </div>
                <div class="-mt-1 ml-2.5 text-left">
                    <span :class="selected('standard') ? 'text-primary' : 'text-sysbase'">{{ __('cms.klassiek') }}</span>
                    <p class="note text-sm">{{ __('cms.klassiek_omschrijving') }}</p>
                </div>
            </button>
            <button class="group-type"
                    :class="selected('carousel') ? 'active' : ''"
                    @click="select('carousel')"
            >
                <div>
                    <x-stickers.group-carousel/>
                </div>
                <div x-show="selected('carousel')">
                    <x-icon.checkmark-circle/>
                </div>
                <div class="-mt-1 ml-2.5 text-left">
                    <span :class="selected('carousel') ? 'text-primary' : 'text-sysbase'">{{ __('cms.carrousel') }}</span>
                    <p class="note text-sm">{{ __('cms.carrousel_omschrijving') }}</p>
                </div>
            </button>
        </div>
    </x-input.group>


    <x-input.group class="text-base" label="{{ __('cms.Omschrijving') }}">
    <x-input.rich-textarea
            wire:model.debounce.1000ms="question.question"
            editorId="{{ $questionEditorId }}"
            type="cms"
    />
    </x-input.group>
@endsection

@section('upload-section-for-group-question')
    <x-partials.question-question-section></x-partials.question-question-section>
@endsection
