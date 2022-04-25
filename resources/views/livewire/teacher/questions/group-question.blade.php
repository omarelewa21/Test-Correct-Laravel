@extends('livewire.teacher.questions.cms-layout')
@section('question-cms-group-question')
    <x-input.group label="{{ __('cms.naam vraaggroep') }}">

        <input type="text" wire:model="question.name" class="form-input w-full text-left @error('question.name') border border-allred @enderror"/>
    </x-input.group>


    <x-input.group
        label="{{ __('cms.type vraaggroep') }}"
        x-data="{
            value: window.Livewire.find(document.getElementById('cms').getAttribute('wire:id')).entangle('question.groupquestion_type'),
            select: function(option) {
                this.value = option;
            },
            selected: function(option){
                return option == this.value;
            },
        }"
    >
        <div class="flex">
            <button
                class="flex flex w-[308] h-[88] relative inline-flex items-center p-4 select-button mr-8 mb-8"
                :class="value=='standard'?'btn-active':'note'"
                @click="select('standard')"
            >
                <svg class="1/5 " width="46" height="46" viewBox="0 0 46 46" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <filter x="-1.7%" y="-2.6%" width="103.4%" height="105.2%" filterUnits="objectBoundingBox"
                                id="d706hd143a">
                            <feOffset dy="3" in="SourceAlpha" result="shadowOffsetOuter1"/>
                            <feGaussianBlur stdDeviation="3" in="shadowOffsetOuter1" result="shadowBlurOuter1"/>
                            <feColorMatrix
                                values="0 0 0 0 0.0156862745 0 0 0 0 0.121568627 0 0 0 0 0.454901961 0 0 0 0.1 0"
                                in="shadowBlurOuter1" result="shadowMatrixOuter1"/>
                            <feMerge>
                                <feMergeNode in="shadowMatrixOuter1"/>
                                <feMergeNode in="SourceGraphic"/>
                            </feMerge>
                        </filter>
                    </defs>
                    <g filter="url(#d706hd143a)" transform="translate(-56 -220)" fill="none" fill-rule="evenodd">
                        <g stroke="currentColor" stroke-width="2">
                            <path d="M93 237a3 3 0 0 1-3 3H69a4 4 0 0 0-4 4v1h0" stroke-linecap="square"
                                  stroke-linejoin="round"/>
                            <path stroke-linecap="round" d="m68 244-3 3-3-3"/>
                        </g>
                        <g stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" d="m80 226 3 3-3 3"/>
                            <path stroke-linecap="square" d="M72 229h10"/>
                        </g>
                        <g stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" d="m80 255 3 3-3 3"/>
                            <path stroke-linecap="square" d="M73 258h9"/>
                        </g>
                        <g transform="translate(56 220)">
                            <circle fill="currentColor" cx="9" cy="9" r="9"/>
                            <text font-family="Nunito-Bold, Nunito" font-size="9" font-weight="bold"
                                  letter-spacing=".18" fill="#FFF">
                                <tspan x="6.21" y="12">1</tspan>
                            </text>
                        </g>
                        <g transform="translate(84 220)">
                            <circle stroke="currentColor" stroke-width="2" cx="9" cy="9" r="8"/>
                            <text font-family="Nunito-Bold, Nunito" font-size="9" font-weight="bold"
                                  letter-spacing=".18" fill="currentColor">
                                <tspan x="6.21" y="12">2</tspan>
                            </text>
                        </g>
                        <g transform="translate(84 248)">
                            <circle stroke="currentColor" stroke-width="2" cx="9" cy="9" r="8"/>
                            <text font-family="Nunito-Bold, Nunito" font-size="9" font-weight="bold"
                                  letter-spacing=".18" fill="currentColor">
                                <tspan x="6.21" y="12">4</tspan>
                            </text>
                        </g>
                        <g transform="translate(56 248)">
                            <circle stroke="currentColor" stroke-width="2" cx="9" cy="9" r="8"/>
                            <text font-family="Nunito-Bold, Nunito" font-size="9" font-weight="bold"
                                  letter-spacing=".18" fill="currentColor">
                                <tspan x="6.21" y="12">3</tspan>
                            </text>
                        </g>
                    </g>
                </svg>
                <div x-show="selected('standard')">
                    <x-icon.checkmark-circle></x-icon.checkmark-circle>
                </div>
                <div class="ml-4 w-4/5 text-left">
                    <span class="">{{ __('cms.klassiek') }}</span>
                    <p class="note text-sm">{{ __('cms.klassiek_omschrijving') }}</p>
                </div>
            </button>
            <button
                class="flex w-[308] h-[88]  relative inline-flex items-center p-4 select-button mb-8"
                :class="value=='carousel'?'btn-active':'note'"
                @click="select('carousel')"
            >

                <svg class="w-1/5" width="47" height="46" viewBox="0 0 47 46" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <filter x="-1.7%" y="-2.6%" width="103.4%" height="105.2%" filterUnits="objectBoundingBox"
                                id="rydtvb381a">
                            <feOffset dy="3" in="SourceAlpha" result="shadowOffsetOuter1"/>
                            <feGaussianBlur stdDeviation="3" in="shadowOffsetOuter1" result="shadowBlurOuter1"/>
                            <feColorMatrix
                                values="0 0 0 0 0.0156862745 0 0 0 0 0.121568627 0 0 0 0 0.454901961 0 0 0 0.1 0"
                                in="shadowBlurOuter1" result="shadowMatrixOuter1"/>
                            <feMerge>
                                <feMergeNode in="shadowMatrixOuter1"/>
                                <feMergeNode in="SourceGraphic"/>
                            </feMerge>
                        </filter>
                    </defs>
                    <g filter="url(#rydtvb381a)" transform="translate(-372 -220)" fill="none" fill-rule="evenodd">
                        <path
                            d="M400.904 225.825c-6.733-2.115-14.374-.138-19.193 5.605M412.782 248.048a18.073 18.073 0 0 0-.43-11.38M388.777 259.703a18.053 18.053 0 0 0 13.558-.047"
                            stroke="currentColor" stroke-width="2"/>
                        <g transform="translate(372.5 220)">
                            <circle fill="currentColor" cx="9" cy="9" r="9"/>
                            <text font-family="Nunito-Bold, Nunito" font-size="9" font-weight="bold"
                                  letter-spacing=".18" fill="#FFF">
                                <tspan x="6.21" y="12">2</tspan>
                            </text>
                        </g>
                        <g transform="translate(400.5 220)">
                            <circle stroke="currentColor" stroke-width="2" cx="9" cy="9" r="8"/>
                            <text font-family="Nunito-Bold, Nunito" font-size="9" font-weight="bold"
                                  letter-spacing=".18" fill="currentColor">
                                <tspan x="6.21" y="12">5</tspan>
                            </text>
                        </g>
                        <g transform="translate(400.5 248)">
                            <circle stroke="currentColor" stroke-width="2" cx="9" cy="9" r="8"/>
                            <text font-family="Nunito-Bold, Nunito" font-size="9" font-weight="bold"
                                  letter-spacing=".18" fill="currentColor">
                                <tspan x="6.21" y="12">1</tspan>
                            </text>
                        </g>
                        <g transform="translate(372.5 248)">
                            <circle stroke="currentColor" stroke-width="2" cx="9" cy="9" r="8"/>
                            <text font-family="Nunito-Bold, Nunito" font-size="9" font-weight="bold"
                                  letter-spacing=".18" fill="currentColor">
                                <tspan x="6.21" y="12">8</tspan>
                            </text>
                        </g>
                        <path stroke="currentColor" stroke-width="2" stroke-linecap="round"
                              d="m416.472 246.053-3.674 2.121-2.122-3.674M391.883 263.832l-1.793-3.845 3.846-1.793M398.544 222.285l2.433 3.475-3.475 2.434"/>
                    </g>
                </svg>
                <div x-show="selected('carousel')">
                    <x-icon.checkmark-circle></x-icon.checkmark-circle>
                </div>
                <div class="ml-4 w-4/5 text-left">
                    <span class="">{{ __('cms.carrousel') }}</span>
                    <p class="note text-sm">{{ __('cms.carrousel_omschrijving') }}</p>
                </div>
            </button>
        </div>
    </x-input.group>



    <x-input.rich-textarea
        wire:model.debounce.1000ms="question.question"
        editorId="{{ $questionEditorId }}"
        type="cms"
    />
@endsection

@section('upload-section-for-group-question')
    <x-partials.question-question-section></x-partials.question-question-section>
@endsection
