@extends($preview ?? 'livewire.teacher.questions.cms-layout')
@section('question-cms-question')
    <x-input.rich-textarea
            wire:model.debounce.1000ms="question.question"
            editorId="{{ $questionEditorId }}"
            type="cms"
            lang="{{ $lang }}"
            :allowWsc="$allowWsc"
            :disabled="isset($preview)"
    />
@endsection

@section('question-cms-answer')
    <div class="relation-answer-list | ">
        <div class="relation-question-toggles | ">
            <div class="border-b border-bluegrey flex w-full justify-between items-center h-[50px]">
                <div class="flex items-center gap-2.5">
                    <x-input.toggle class="mr-2" wire:model="question.random_per_student" />
                    <x-icon.shuffle />
                    <span class="bold">@lang('cms.Carrousel verdeling per student')</span>
                </div>
                <div class="flex items-center gap-2">
                    <x-input.text class="text-center w-[3.375rem]"
                                  :only-integer="true"
                                  wire:model.lazy="question.random_per_student_amount"
                                  :disabled="!$this->question['random_per_student']"
                                  :error="$this->getErrorBag()->has('random_per_student_amount')"
                    />
                    <x-tooltip>@lang('cms.relation_carousel_tooltip')</x-tooltip>
                </div>
            </div>
        </div>
        <div class="relation-question-intro | mt-6 mb-4">
            <p>Kies wat de student ziet (vraagstelling). Je kan dit per categorie (kolom) aangeven, maar ook speciferen
                per woord (cel) door daarop te klikken. Kies je bijv. synoniem, definitie, etc. als vraagstelling, dan
                zal de student het taalvak woord moeten antwoorden.</p>
        </div>
        <div class="relation-question-grid-container | ">
            <div class="relation-question-grid | "
                 style="--relation-grid-cols: @js(count($this->cmsPropertyBag['words'][0]))">
                <div class="grid-head-container contents">
                    <div class="grid-head">
                        <x-input.radio value="main"
                                       name="relation-column"
                                       text-left="main"
                                       label-classes="bold gap-2 hover:text-primary"
                        />

                    </div>
                    <div class="grid-head">
                        <x-input.radio value="translation"
                                       name="relation-column"
                                       text-left="translation"
                                       label-classes="bold gap-2 hover:text-primary"
                        />
                    </div>
                    <div class="grid-head">
                        <x-input.radio value="definition"
                                       name="relation-column"
                                       text-left="definition"
                                       label-classes="bold gap-2 hover:text-primary"
                        />
                    </div>
                    <div class="grid-head">
                        <x-input.radio value="synonym"
                                       name="relation-column"
                                       text-left="synonym"
                                       label-classes="bold gap-2 hover:text-primary"
                        />
                    </div>
                    <div class="grid-head">
                        <x-input.radio value="ditjes"
                                       name="relation-column"
                                       text-left="ditjes"
                                       label-classes="bold gap-2 hover:text-primary"
                        />
                    </div>
                </div>

                <div class="grid-divider"></div>

                @foreach($this->cmsPropertyBag['words'] as $word)
                    <div class="word-row contents">
                        <div>
                            <span>{{ $word['main'] }}</span>
                            <x-icon.checkmark-small class="text-white min-w-[13px]"/>
                        </div>
                        <div>
                            <span>{{ $word['translation'] }}</span>
                            <x-icon.checkmark-small class="text-white min-w-[13px]"/>
                        </div>
                        <div>
                            <span>{{ $word['definition'] }}</span>
                            <x-icon.checkmark-small class="text-white min-w-[13px]"/>
                        </div>
                        <div>
                            <span>{{ $word['synonym'] }}</span>
                            <x-icon.checkmark-small class="text-white min-w-[13px]"/>
                        </div>
                        <div>
                            <span>{{ $word['synonym'] }}</span>
                            <x-icon.checkmark-small class="text-white min-w-[13px]"/>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection
