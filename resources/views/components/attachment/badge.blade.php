@props([
    'title' => 'unkown',
    'upload',
    'attachment',
    'disabled' => false
])

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
    if($type == 'video') {
        $host = $this->getVideoHost($attachment->link);
    }
@endphp

<div class="flex border rounded-lg border-blue-grey items-center mr-4 mb-2"
     x-data="badge('{{ $type == 'video' ? $attachment->link : null }}')"
     wire:key="{{ $attributes['wire:key'] }}"
     @attachments-updated.window="setIndex()"
>
    <div class="flex p-2 border-r border-blue-grey h-full items-center">
        @if($type == 'image')
            <x-icon.image/>
        @elseif($type == 'video')
            @if($host === 'vimeo')
                <x-icon.vimeo/>
            @else
                <x-icon.youtube/>
            @endif
        @elseif($type == 'audio')
            <x-icon.audiofile/>
        @elseif($type == 'pdf')
            <x-icon.pdf/>
        @else
            <x-icon.attachment/>
        @endif
    </div>
    <div class="flex base items-center relative">
        <span class="pl-2" x-text="index + ':'"></span>
        @if($type == 'video')
        <span class="p-2 text-base max-w-[200px] truncate"
              :class="{'text-midgrey': resolvingTitle}"
              :title="videoTitle"
              x-text="videoTitle"
        >
        </span>
        @else
        <span class="p-2 text-base max-w-[200px] truncate" title="{{ $title }}">
            {{ $title }}
        </span>
        @endif
        @if($disabled)
            <button class="py-3 px-4 flex items-center h-full rounded-md text-midgrey transition"
            >
                <x-icon.options/>
            </button>
        @else

        <button class="py-3 px-4 flex items-center h-full rounded-md hover:bg-primary hover:text-white transition"
                @click="options = true"
        >
            <x-icon.options/>
        </button>

            <div x-cloak
                 x-show="options"
                 x-ref="optionscontainer"
                 class="absolute right-0 top-10 bg-white py-2 main-shadow rounded-10 w-72 z-20"
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
                                            :checked="$attachment->hasSetting('play_once', $this->questionId) === '1'"
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
                                            :checked="$attachment->hasSetting('pausable', $this->questionId) === '1'"
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
                                            class="w-24 pr-10 text-base"
                                            placeholder="250"
                                            @change="$wire.handleUploadSettingChange('timeout', $event.target.value, '{{ $title }}')"
                                    />
                                @else
                                    <x-input.text
                                            type="number"
                                            maxlength="4"
                                            class="w-24 pr-10 text-base"
                                            placeholder="250"
                                            @change="$wire.handleAttachmentSettingChange({'timeout': $event.target.value}, '{{ $attachment->uuid }}')"
                                            :value="$attachment->hasSetting('timeout', $this->questionId)"
                                    />
                                @endif
                                <span class="audio-seconds-input"></span>
                            </div>
                        </div>
                    </div>
                    <div class="flex w-full h-px bg-blue-grey mb-2"></div>
                @endif
                <button class="flex items-center space-x-2 py-1 px-4 base hover:text-primary hover:bg-offwhite transition w-full"
                        @click="$dispatch('delete-modal', ['{{ $upload ? 'upload' : 'attachment'}}', '{{ $upload ? $attachment->getFileName() : $attachment->uuid }}'])"
                >
                    <x-icon.remove/>
                    <span class="text-base bold inherit">{{ __('cms.Verwijderen') }}</span>
                </button>
            </div>
        @endif



    </div>
</div>