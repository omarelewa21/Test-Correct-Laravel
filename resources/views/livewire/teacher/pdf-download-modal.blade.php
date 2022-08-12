<x-modal.base-modal x-data="{
                value : null,
                activateAttachmentsLink: '{{ $testHasPdfAttachments }}',
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
                        case 'attachments':
                            $wire.emit('openModal', 'teacher.test-pdf-attachments-download-modal', {test: '9d4b1ace-d8ef-419f-a844-c295bbb9b5f4'});
                            {{--this.export_attachments();--}}
                            break;
                        case 'testpdf':
                            this.export_test_pdf();
                            break;
                        case 'answermodel':
                            this.export_answer_model_pdf();
                            break;
                    }
{{--                    $wire.emit('closeModal');--}}
                },
                export_attachments: async function (){
                    let response = await $wire.getTemporaryLoginToPdfForTest();
                    window.open(response, '_blank');
                },
                export_test_pdf: function () {
                    var windowReference = window.open();
                    windowReference.document.write(this.waitingScreenHtml);
                    windowReference.location = '{{ route('teacher.preview.test_pdf', ['test' => $uuid]) }}';
                },
                export_answer_model_pdf: function () {
                    var windowReference = window.open();
                    windowReference.document.write(this.waitingScreenHtml);
                    windowReference.location = '{{ route('teacher.test-answer-model', ['test' => $uuid]) }}';
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
                        :class="{'active': selected('testpdf')}"
                        @click="select('testpdf')"
                >
                    <div class="flex">
                        <x-stickers.test-update/>
                    </div>

                    <div x-show="selected('testpdf')">
                        <x-icon.checkmark class="absolute top-2 right-2 overflow-visible"/>
                    </div>
                    <div class="ml-2.5 text-left">
                        <span class="text-base bold">{{ __('cms.toets_pdf') }}</span>
                        <p class="note text-sm">{{ __('cms.toets_pdf_omschrijving') }}</p>
                    </div>
                </button>

                <button class="test-change-option transition"
                        :class="{'active': selected('attachments') && activateAttachmentsLink, 'opacity-25': ! activateAttachmentsLink }"
                        @click="activateAttachmentsLink ? select('attachments') : ''"
                >
                    <div>
                        <x-stickers.test-new/>
                    </div>
                    <div x-show="selected('attachments') && activateAttachmentsLink">
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
                        <x-stickers.test-new/>
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
                        {{-- (student-)Answers button/card is disabled, pdf doesnt exist yet --}}
                        {{--     :class="{'active': selected('studentanswers')}"--}}
                        {{--     @click="select('studentanswers')"--}}
                >
                    <div>
                        <x-stickers.test-new/>
                    </div>
                    {{-- (student-)Answers button/card is disabled, pdf doesnt exist yet --}}
                    {{--      <div x-show="selected('studentanswers')">--}}
                    {{--           <x-icon.checkmark class="absolute top-2 right-2 overflow-visible"/>--}}
                    {{--      </div>--}}
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
                <x-button.text-button wire:click="close">{{ __('modal.sluiten') }}</x-button.text-button>
                <x-button.cta @click="export_pdf(value)">{{ __('cms.pdf_exporteren') }}</x-button.cta>
            </div>
        </div>
    </x-slot>
</x-modal.base-modal>
