<x-modal.base-modal>
    <x-slot name="title">
        <h2>
        @if($this->title)
           {{ $this->title }}
        @elseif($this->type === 'warning')
            {{__("modal.warning")}}
        @endif
        </h2>
    </x-slot>
    <x-slot name="content">
        {{ $this->message }}

    </x-slot>
    <x-slot name="footer">
        @if ($this->primaryAction)
            <div class="flex justify-end w-full gap-4 items-center">
                <x-button.text wire:click="$emit('closeModal')">
                    <span>{{ __('modal.annuleren') }}</span>
                </x-button.text>
                <x-button.cta type="link" href="{{ $this->primaryAction }}">
                    <span>{{ $this->primaryActionBtnLabel }} </span>
                </x-button.cta>
            </div>
        @else
            <div class="flex justify-end w-full">
                <x-button.primary wire:click="$emit('closeModal')">
                    <span>{{ __('modal.annuleren') }}</span>
                </x-button.primary>
            </div>
        @endif
    </x-slot>

</x-modal.base-modal>
