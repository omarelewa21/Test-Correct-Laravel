<x-modal-new>
    <x-slot name="title">
        {{__("general.Wissel van school")}}
    </x-slot>
    <x-slot name="body">
        <div class="items-center mt-4 space-y-2">
                @foreach($schoolLocations as $uuid => $schoolLocation)
                <div class="flex items-center flex-col" title="{{ $schoolLocation }}">
                    <label wire:click="switchToSchoolLocation('{{ $uuid }}')" wire:key="label_{{ $uuid }}"
                           class=" relative w-full flex hover:font-bold p-5 border-2 border-blue-grey rounded-10 base
                                    multiple-choice-question transition ease-in-out duration-150 focus:outline-none
                                    justify-between {!! (auth()->user()->schoolLocation->uuid == $uuid) ? 'active' :'' !!}
                                   ">
                    <div class="truncate" id="mc_c_answertext_{{$uuid}}" wire:key="text_{{$uuid}}" >{{ $schoolLocation }}</div>
                    <div id="checkmark_{{ $uuid }}" wire:key="checkmark_{{$uuid}}" class="{!! (auth()->user()->schoolLocation->uuid == $uuid)  ? '' :'hidden' !!}">
                        <x-icon.checkmark/>
                    </div>
                </div>
            @endforeach
        </div>
    </x-slot>
    <x-slot name="footer">
        <div class="flex justify-end">


          {{-- 44vw depends on maxWidth 2xl... --}}
        </div>
    </x-slot>
</x-modal-new>