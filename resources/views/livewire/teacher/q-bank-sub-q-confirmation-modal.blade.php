<x-modal.base-modal wire:model="showModal">
    <x-slot name="title"><h2>{{ __('teacher.QBank_subQ_confiramtion_title') }}.</h2></x-slot>
    <x-slot name="content">{{ __('teacher.QBank_subQ_confiramtion_text') }} ?</x-slot>
    <x-slot name="footer">
        <div class="flex justify-end w-full gap-4"
        x-data="{
            addQuestionToTest: (button) => {
                button.disabled = true;
                $wire.addQuestionToTest();
                setTimeout(()=>{
                    $wire.emit('closeModal');
               }, 1000);
            }
        }">
            <x-button.text-button wire:click="$emit('closeModal')"><span>{{ __('teacher.Annuleer') }}</span>
            </x-button.text-button>

            <x-button.cta @click="addQuestionToTest($el)"><span>{{ __('teacher.add') }}</span></x-button.cta>
        </div>
    </x-slot>
</x-modal.base-modal>
