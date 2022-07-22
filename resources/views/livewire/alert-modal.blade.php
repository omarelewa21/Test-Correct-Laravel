<x-modal-new force-close="true">
    <x-slot name="title">
        @if($this->title)
           {{ $this->title }}


        @elseif($this->type === 'warning')
            {{__("modal.warning")}}
        @endif
    </x-slot>
    <x-slot name="body">
        {{ $this->message }}

    </x-slot>
    <x-slot name="footer">
        @if ($this->primaryAction)
            <div class="flex justify-between w-full">
                <x-button.text-button wire:click="$emit('closeModal')">
                    <span>{{ __('modal.annuleren') }}</span>
                </x-button.text-button>
                <x-button.cta type="link" href="{{ $this->primaryAction }}">
                    <span>{{ $this->primaryActionBtnLabel }} </span>
                </x-button.cta>
            </div>
        @else
            <div class="flex justify-end w-full">
                <x-button.text-button wire:click="$emit('closeModal')">
                    <span>{{ __('modal.annuleren') }}</span>
                </x-button.text-button>
            </div>
        @endif
    </x-slot>

</x-modal-new>
