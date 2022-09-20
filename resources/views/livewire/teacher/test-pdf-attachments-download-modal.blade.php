<x-modal.base-modal x-data="{
                value : null,
                select: function(attachment, question) {
                    this.value = [attachment, question];
                },
                selected: function(attachment){
                    if(!this.value){
                        return false
                    }
                    return attachment === this.value[0];
                },
                downloadPdfAttachment: async function(){
                    url = await $wire.getRoute(this.value);
                    window.open(url);
                }
}">
    <x-slot name="title">
        <h2>{{__("teacher.Toets pdf bijlage exporteren")}}</h2>
    </x-slot>
    <x-slot name="content">
        @if($displayValueRequiredMessage)
            <div class="mb-4 text-red-500 text-sm">{{ __('cms.Kies een waarde') }}</div>
        @endif
        <div class="flex">
            <div name="block-container" class="items-center mt-4 space-y-2 w-full">
                <div class="col-span-1">
                    {{ __('teacher.Kies een pdf attachment') }}
                </div>
                @foreach($this->pdfAttachments as $attachment)
                    <div class="flex items-center flex-col" title="{{ $attachment->uuid }}">
                        <label @click="select('{{$attachment->uuid}}', '{{$attachment->questionUuid}}')" wire:key="label_{{ $attachment->uuid }}"
                               class=" relative w-full flex hover:font-bold p-5 border-2 border-blue-grey rounded-10 base
                                    multiple-choice-question transition ease-in-out duration-150 focus:outline-none
                                    justify-between
                                   "
                               :class="{active: selected('{{$attachment->uuid}}')}"
                        >
                            <div class="truncate" id="mc_c_answertext_{{$attachment->uuid}}" wire:key="text_{{$attachment->uuid}}" >{{ $attachment->title }}</div>
                            <div id="checkmark_{{ $attachment->uuid }}" wire:key="checkmark_{{$attachment->uuid}}" :class="{hidden: !selected('{{$attachment->uuid}}') }">
                                <x-icon.checkmark/>
                            </div>
                        </label>
                    </div>
                @endforeach

            </div>
        </div>
    </x-slot>
    <x-slot name="footer">
        <div class="flex justify-end items-center">
            <div class="flex gap-4">
                <x-button.text-button wire:click="close">{{ __('modal.sluiten') }}</x-button.text-button>
                <x-button.cta @click="downloadPdfAttachment()">{{ __('cms.pdf_exporteren') }}</x-button.cta>
            </div>
        </div>
    </x-slot>
</x-modal.base-modal>
