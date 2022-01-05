@php
    $type =  '';
    if($upload) {
        $type = collect(explode('/', $attachment->getMimeType()))->first();

        if ($type == 'application') {
            $type = 'pdf';
        }
    } else {
        $type = $attachment->getFileType();
    }
@endphp

<div class="flex border rounded-lg border-blue-grey items-center mr-4 mb-2"
     x-data="{options: false}"
     x-init="
        $watch('options', value => {
            if (value) {
                let pWidth = $refs.optionscontainer.parentElement.offsetWidth;
                let pPos = $refs.optionscontainer.parentElement.getBoundingClientRect().left;
                if ((pWidth + pPos) < 288) {
                    $refs.optionscontainer.classList.remove('right-0');
                }
            }
        })
     "
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
        @if($type == 'video')
        <span class="p-2 text-base max-w-[200px] truncate" title="{{ $attachment->link }}">
            {{ $attachment->link }}
        </span>
        @else
        <span class="p-2 text-base max-w-[200px] truncate" title="{{ $title }}">
            {{ $title }}
        </span>
        @endif
        <button class="py-3 px-4 flex items-center h-full rounded-md hover:bg-primary hover:text-white transition"
                @click="options = true"
        >
            <x-icon.options/>
        </button>

        <div x-cloak
             x-show="options"
             x-ref="optionscontainer"
             class="absolute right-0 top-10 bg-white py-2 main-shadow rounded-10 w-72 z-10"
             @click.outside="options=false"
             x-transition:enter="transition ease-out origin-top-right duration-200"
             x-transition:enter-start="opacity-0 transform scale-90"
             x-transition:enter-end="opacity-100 transform scale-100"
             x-transition:leave="transition origin-top-right ease-in duration-100"
             x-transition:leave-start="opacity-100 transform scale-100"
             x-transition:leave-end="opacity-0 transform scale-90"
        >
            @if($type == 'audio')
                <div class="flex flex-col w-full px-4 divide-y divide-bluegrey">
                    <div class="flex justify-between w-full items-center py-[11px]">
                        <span class="text-base bold">{{ __('cms.Eenmalig afspelen') }}</span>
                        <div class="flex items-center">
                            <x-tooltip class="mr-2">
                                <span class="text-base text-left">
                                    {{ __('cms.Eenmalig afspelen') }}
                                </span>
                            </x-tooltip>
                            @if($upload)
                                <x-input.toggle
                                        @change="$wire.handleUploadSettingChange('play_once', $event.target.checked ? '1' : '0', '{{ $title }}')"/>
                            @else
                                <x-input.toggle
                                        @change="$wire.handleAttachmentSettingChange({'play_once': $event.target.checked ? '1' : '0'}, '{{ $attachment->uuid }}')"
                                        :checked="optional(json_decode($attachment->json))->play_once === '1'"
                                />
                            @endif
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
                            @if($upload)
                                <x-input.toggle
                                        @change="$wire.handleUploadSettingChange('pausable', $event.target.checked ? '1' : '0', '{{ $title }}')"/>
                            @else
                                <x-input.toggle
                                        @change="$wire.handleAttachmentSettingChange({'pausable': $event.target.checked ? '1' : '0'}, '{{ $attachment->uuid }}')"
                                        :checked="optional(json_decode($attachment->json))->pausable === '1'"
                                />
                            @endif
                        </div>
                    </div>
                    <div class="flex justify-between w-full items-center py-[5px]">
                        <span class="text-base bold">{{ __('cms.Antwoordtijd') }}</span>
                        <div class="flex items-center relative">
                            <x-tooltip class="mr-2">
                                <span class="text-base text-left">
                                    {{ __('cms.Antwoordtijd') }}
                                </span>
                            </x-tooltip>
                            @if($upload)
                                <x-input.text
                                        type="number"
                                        maxlength="4"
                                        class="w-24 pr-10"
                                        placeholder="250"
                                        @change="$wire.handleUploadSettingChange('timeout', $event.target.value, '{{ $title }}')"
                                />
                            @else
                                <x-input.text
                                        type="number"
                                        maxlength="4"
                                        class="w-24 pr-10"
                                        placeholder="250"
                                        @change="$wire.handleAttachmentSettingChange({'timeout': $event.target.value}, '{{ $attachment->uuid }}')"
                                        :value="optional(json_decode($attachment->json))->timeout"
                                />
                            @endif
                            <span class="audio-seconds-input"></span>
                        </div>
                    </div>
                </div>
                <div class="flex w-full h-px bg-blue-grey mb-2"></div>
            @endif
            <button class="flex items-center space-x-2 py-1 px-4 base hover:text-primary hover:bg-offwhite transition w-full"
                 @if($upload)
                 wire:click="removeFromUploads('{{ $title }}')"
                 @else
                 wire:click="removeAttachment('{{ $attachment->uuid }}')"
                 @endif
                 @click="options = false"
            >
                <x-icon.trash/>
                <span class="text-base bold inherit">{{ __('cms.Verwijderen') }}</span>
            </button>
        </div>

    </div>
</div>

