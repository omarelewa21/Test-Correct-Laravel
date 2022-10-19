<x-modal.base-modal wire:model="showModal">
    <x-slot name="title"><h2>{{__('student.warning')}}</h2></x-slot>
    <x-slot name="content">
        <span>{{ __('teacher.question_bank_add_confirmation_text') }}.</span> </br>
        <span>{{ __('teacher.question_bank_add_confirmation_sub_text') }}?</span>
    </x-slot>
    <x-slot name="footer">
        <div class="flex justify-end w-full gap-4"
            x-data="{
                addToTest: async (button, questionUuid) => {
                    let close = await addQuestionToTest(button, questionUuid);
                    if(close) $wire.emit('closeModal', {force: true, skipPreviousModals: true});
                }
            }">
            <x-button.text-button wire:click="$emit('closeModal')"><span>{{ __('teacher.Annuleer') }}</span>
            </x-button.text-button>

            <x-button.cta @click="addToTest($el, '{{$questionUuid}}')">
                <span>{{ __('teacher.add') }}</span>
            </x-button.cta>
        </div>
    </x-slot>
</x-modal.base-modal>
