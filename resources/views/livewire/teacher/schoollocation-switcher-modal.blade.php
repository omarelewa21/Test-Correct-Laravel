<x-modal-new>
    <x-slot name="title">
        {{__("general.Wissel van school")}}
    </x-slot>
    <x-slot name="body">
        <div class="items-center">
                @foreach($schoolLocations as $uuid => $schoolLocation)
                    @if(auth()->user()->schoolLocation->uuid == $uuid)
                        <x-button.primary
                                class="block"
                                >
                            {{ $schoolLocation }}
                            </x-button-primary>
                            @else
                                <x-button.text-button
                                        style="padding: 0 20px"
                                        class="block space-x-2.5"
                                        wire:click="switchToSchoolLocation('{{ $uuid }}')">
                                    {{ $schoolLocation }}
                                </x-button.text-button>
                    @endif
                @endforeach
        </div>
    </x-slot>
    <x-slot name="footer">
        <div class="flex justify-end">

                <x-button.text-button wire:click="closeModal">{{ __('Annuleren') }}</x-button.text-button>
          {{-- 44vw depends on maxWidth 2xl... --}}
        </div>
    </x-slot>
</x-modal-new>