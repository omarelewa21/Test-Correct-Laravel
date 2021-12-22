@php
    $type =  '';
    if(!$upload) {
        $type = $attachment->getFileType();
    }
@endphp
@if($upload)
    <button onclick="window.open('{{ $attachment->temporaryUrl() }}', '_blank')"
            class="flex border rounded-lg border-blue-grey items-center mr-4 mb-2"
            x-data="{options: false}"
    >
        <div class="flex p-2 border-r border-blue-grey h-full items-center">
            @if($type == 'image')
                <x-icon.image/>
            @elseif($type == 'video')
                <x-icon.youtube/>
            @elseif($type == 'audio')
                <x-icon.audiofile/>
            @elseif($type == 'pdf')
                <x-icon.pdf/>
            @else
                <x-icon.attachment/>
            @endif
        </div>
        <div class="flex base items-center relative">
            <span class="p-2 text-base max-w-[200px] truncate"
                  title="{{ $attachment->getClientOriginalName() }}">
                {{ $attachment->getClientOriginalName() }}
            </span>
            <span class="py-3 px-4 flex items-center h-full rounded-md hover:bg-primary hover:text-white transition"
                  @click="options = true"
                  @click.outside="options=false"
            >
                <x-icon.options/>
            </span>

            <template x-if="options">
                <div class="hidden absolute right-0 top-10 bg-white">
                    <span>hallo</span>
                </div>
            </template>
        </div>
    </button>
@else
    <button class="flex border rounded-lg border-blue-grey items-center mr-4 mb-2"
            x-data="{options: false}"
    >
        <div class="flex p-2 border-r border-blue-grey h-full items-center">
            @if($type == 'image')
                <x-icon.image/>
            @elseif($type == 'video')
                <x-icon.youtube/>
            @elseif($type == 'audio')
                <x-icon.audiofile/>
            @elseif($type == 'pdf')
                <x-icon.pdf/>
            @else
                <x-icon.attachment/>
            @endif
        </div>
        <div class="flex base items-center relative">
            <span class="p-2 text-base max-w-[200px] truncate"
                  title="{{ $attachment->title }}">{{ $attachment->title }}
            </span>
            <span class="py-3 px-4 flex items-center h-full rounded-md hover:bg-primary hover:text-white transition"
                  @click="options = true;"
            >
                <x-icon.options/>
            </span>

            <div x-cloak x-show="options" class="absolute right-0 top-10 bg-white py-1 main-shadow rounded-10 w-72 z-10"
                 @click.outside="options=false"
            >
{{--                @if($type == 'audio')--}}
                <div class="flex flex-col w-full px-4 divide-y divide-bluegrey">
                    <div class="flex justify-between w-full items-center py-[11px]">
                        <span class="text-base bold">{{ __('cms.Eenmalig afspelen') }}</span>
                        <div class="flex items-center">
                            <x-tooltip class="mr-2">
                                <span class="text-base text-left">
                                    {{ __('cms.Eenmalig afspelen') }}
                                </span>
                            </x-tooltip>
                            <x-input.toggle
                                    @change="$wire.handleAttachmentSettingChange({play_once: $event.target.checked ? 1 : 0}, {{ $attachment->getKey() }})"
                                    :checked="optional(json_decode($attachment->json))->play_once"
                            />
                        </div>
                    </div>
                    <div class="flex justify-between w-full items-center py-[11px]">
                        <span class="text-base bold">{{ __('cms.Pauzeerbaar') }}</span>
                        <div class="flex items-center">
                            <x-tooltip class="mr-2">
                                <span class="text-base text-left">
                                    {{ __('cms.Pauzeerbaar') }}
                                </span>
                            </x-tooltip>
                            <x-input.toggle/>
                        </div>
                    </div>
                    <div class="flex justify-between w-full items-center py-[11px]">
                        <span class="text-base bold">{{ __('cms.Antwoordtijd') }}</span>
                        <div class="flex items-center">
                            <x-tooltip class="mr-2">
                                <span class="text-base text-left">
                                    {{ __('cms.Antwoordtijd') }}
                                </span>
                            </x-tooltip>
                            <x-input.toggle/>
                        </div>
                    </div>
                </div>
                <div class="flex w-full h-px bg-blue-grey mb-2"></div>
{{--                @endif--}}
                <div class="flex items-center space-x-2 py-1 px-4 base hover:text-primary hover:bg-offwhite transition">
                    <x-icon.trash/>
                    <span class="text-base bold inherit">Verwijderen</span>
                </div>
            </div>
        </div>
    </button>
@endif