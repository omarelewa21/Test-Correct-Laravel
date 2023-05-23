<x-modal.base-modal x-data="{
                value : null,
                activateAttachmentsLink: '{{ $test->attachments?->count() > 0 }}',
                activateStudentAnswersLink: '{{ $testTakeHasAnswers }}',
                waitingScreenHtml: PdfDownload.waitingScreenHtml('{{ $translation }}'),
                select: function(option) {
                    this.value = option;
                },
                selected: function(option){
                    return option === this.value;
                },
                export_pdf: function (value){
                    if(!value){
                        $wire.set('displayValueRequiredMessage', true);
                        return;
                    }
                    switch(value) {
                        case 'testattachments':
                            this.export_test_attachments();
                            break;
                        case 'testpdf':
                            this.export_test_pdf();
                            break;
                        case 'testopgavenpdf':
                            this.export_test_opgaven_pdf();
                            break;
                        case 'testtakepdf':
                            this.export_test_take_pdf();
                            break;
                        case 'answermodel':
                            this.export_answer_model_pdf();
                            break;
                        case 'studentanswers':
                            this.export_test_take_student_answers_pdf();
                            break;
                    }
                },
                export_test_pdf: function () {
                    this.export_now('{{ route('teacher.preview.test_pdf', ['test' => $test->uuid]) }}');
                },
                export_test_opgaven_pdf: function () {
                    this.export_now('{{ route('teacher.preview.test_opgaven_pdf', ['test' => $test->uuid]) }}');
                },
                export_test_attachments: function () {
                    window.open('{{ route('teacher.preview.test_attachments', ['test' => $test->uuid]) }}', '_blank');
                },
                export_test_take_pdf: function () {
                    this.export_now('{{ isset($testTake->uuid) ? route('teacher.preview.test_take_pdf', ['test_take' => $testTake->uuid]) : ''}}');
                },
                export_answer_model_pdf: function () {
                    this.export_now('{{ route('teacher.test-answer-model', ['test' => $test->uuid]) }}');
                },
                export_test_take_student_answers_pdf: function () {
                    this.export_now('{{ isset($testTake->uuid) ? route('teacher.preview.test_take', ['test_take' => $testTake->uuid ?? ''] ) : ''}}');
                },
                export_now: function(url) {
                    var isSafari = navigator.userAgent.indexOf('Safari') > -1 && navigator.userAgent.indexOf('Chrome') <= -1
                    if(isSafari){
                        window.open(url);
                        return;
                    }
                    var windowReference = window.open();
                    windowReference.document.write(this.waitingScreenHtml);
                    windowReference.location = url;
                },

}">
    <x-slot name="title">
        <h2>{{__("teacher.Toets exporteren")}}</h2>
    </x-slot>
    <x-slot name="content">
        @if($displayValueRequiredMessage)
            <div class="mb-4 text-red-500 text-sm">{{ __('cms.Kies een waarde') }}</div>
        @endif
        <div class="flex">
            <div name="block-container" class="grid gap-4 grid-cols-2">
                <div class="col-span-2">
                    {{ __('teacher.Kies een of meerdere onderdelen') }}
                </div>

                <button class="test-change-option transition"
                        :class="{'active': selected('testopgavenpdf')}"
                        @click="select('testopgavenpdf')"
                >
                    <div class="flex">
                        <x-stickers.test-export-questions/>
                    </div>

                    <div x-show="selected('testopgavenpdf')">
                        <x-icon.checkmark class="absolute top-2 right-2 overflow-visible"/>
                    </div>
                    <div class="ml-2.5 text-left">
                        <span class="text-base bold">{{ __('cms.toets_opgaven_pdf') }}</span>
                        <p class="note text-sm">{{ __('cms.toets_opgaven_pdf_omschrijving') }}</p>
                    </div>
                </button>

                @if($testTake)
                    <button class="test-change-option transition"
                            :class="{'active': selected('testtakepdf')}"
                            @click="select('testtakepdf')"
                    >
                        <div class="flex">
                            <x-stickers.test-export-questions/>
                        </div>

                        <div x-show="selected('testtakepdf')">
                            <x-icon.checkmark class="absolute top-2 right-2 overflow-visible"/>
                        </div>
                        <div class="ml-2.5 text-left">
                            <span class="text-base bold">{{ __('cms.toets_pdf') }}</span>
                            <p class="note text-sm">{{ __('cms.toets_pdf_omschrijving') }}</p>
                        </div>
                    </button>
                @else
                    <button class="test-change-option transition"
                            :class="{'active': selected('testpdf')}"
                            @click="select('testpdf')"
                    >
                        <div class="flex">
                            <x-stickers.test-export-questions/>
                        </div>

                        <div x-show="selected('testpdf')">
                            <x-icon.checkmark class="absolute top-2 right-2 overflow-visible"/>
                        </div>
                        <div class="ml-2.5 text-left">
                            <span class="text-base bold">{{ __('cms.toets_pdf') }}</span>
                            <p class="note text-sm">{{ __('cms.toets_pdf_omschrijving') }}</p>
                        </div>
                    </button>
                @endif
                <button class="test-change-option transition"
                        :class="{'active': selected('testattachments') && activateAttachmentsLink, 'opacity-25': ! activateAttachmentsLink }"
                        @click="activateAttachmentsLink ? select('testattachments') : ''"
                >
                    <div>
                        <x-stickers.test-export-attachments/>
                    </div>
                    <div x-show="selected('testattachments') && activateAttachmentsLink">
                        <x-icon.checkmark class="absolute top-2 right-2 overflow-visible"/>
                    </div>
                    <div class="ml-2.5 text-left">
                        <span class="text-base bold">{{ __('cms.bijlagen') }}</span>
                        <p class="note text-sm">{{ __('cms.alle bijlagen') }}</p>
                    </div>
                </button>

                <button class="test-change-option transition"
                        :class="{'active': selected('answermodel')}"
                        @click="select('answermodel')"
                >
                    <div>
                        <x-stickers.test-export-answermodel/>
                    </div>
                    <div x-show="selected('answermodel')">
                        <x-icon.checkmark class="absolute top-2 right-2 overflow-visible"/>
                    </div>
                    <div class="ml-2.5 text-left">
                        <span class="text-base bold">{{ __('cms.antwoordmodellen') }}</span>
                        <p class="note text-sm">{{ __('cms.antwoordmodellen_omschrijving') }}</p>
                    </div>
                </button>

                <button class="test-change-option transition opacity-25"
                        :class="{'active': selected('studentanswers') && activateStudentAnswersLink, 'opacity-25': ! activateStudentAnswersLink}"
                        @click="activateStudentAnswersLink ? select('studentanswers') : ''"
                >
                    <div>
                        <x-stickers.test-export-answers/>
                    </div>
                    <div x-show="selected('studentanswers') && activateStudentAnswersLink">
                        <x-icon.checkmark class="absolute top-2 right-2 overflow-visible"/>
                    </div>
                    <div class="ml-2.5 text-left">
                        <span class="text-base bold">{{ __('cms.antwoorden') }}</span>
                        <p class="note text-sm">{{ __('cms.antwoorden_omschrijving') }}</p>
                    </div>
                </button>
            </div>
        </div>
    </x-slot>
    <x-slot name="footer">
        <div class="flex justify-end items-center">
            <div class="flex gap-4">

                <x-button.text-button wire:click="$emit('closeModal')"><span>{{ __('modal.sluiten') }}</span></x-button.text-button>
                <x-button.cta @click="export_pdf(value)"><span>{{ __('cms.pdf_exporteren') }}</span></x-button.cta>
            </div>
        </div>
    </x-slot>
</x-modal.base-modal>
