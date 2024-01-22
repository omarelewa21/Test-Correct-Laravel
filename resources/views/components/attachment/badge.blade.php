@props([
    'title' => 'unkown',
    'upload',
    'attachment',
    'disabled' => false,
    'deleteAction' => null,
    'withNumber' => true,
    'mode' => 'edit',
    'clickOverride' => false,
    'disableAudioTimer' => false,
])

@php
    $type =  '';
    if($upload) {
        $type = \tcCore\Http\Helpers\BaseHelper::getWorkableTypeFromUploadMime($attachment->getMimeType());
    } else {
        $type = $attachment->getFileType();
    }
    if($type == 'video') {
        $host = \tcCore\Attachment::getVideoHost($attachment->link);
    }
    if(isset($this->questionId)) {
        $questionId = $this->questionId;
    }
@endphp

<div @class([
        "flex border rounded-lg bg-white border-blue-grey items-center mr-4 mb-2 group relative",
        "badge-view cursor-pointer" => $mode === 'view',
        $attributes->get('class'),
        ])
     x-data="badge('{{ $type == 'video' ? $attachment->link : null }}', '{{ $mode }}')"
     wire:key="{{ $attributes['wire:key'] }}"
     @attachments-updated.window="setIndex()"
     @if($mode === 'view' && !$clickOverride)
         wire:click="$emit('openModal', 'modal.preview-attachment', {{ json_encode(['attachmentUuid' => $attachment->uuid, 'questionUuid' => $questionUuid ]) }})"
        @endif
     {{ $attributes->except(['class','wire:key']) }}
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
        @elseif($type == 'word')
            <x-icon.word/>
        @else
            <x-icon.attachment/>
        @endif
    </div>
    <div class="flex group-hover:text-primary group-active:text-primary  items-center relative badge-title">
        @if($withNumber)
            <span class="pl-2 select-none" x-text="index + ':'"></span>
        @endif
        @if($type == 'video' && $mode == 'edit')
            <span class="p-2 max-w-[200px] truncate select-none"
                  :class="{'text-midgrey': resolvingTitle}"
                  :title="videoTitle"
                  x-text="videoTitle"
            >
        </span>
        @else
            <span class="p-2 max-w-[200px] truncate select-none" title="{{ $title }}">
            {{ $title }}
        </span>
        @endif
        @if($mode === 'edit')
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
                        <div class="flex flex-col w-full px-4 divide-y divide-bluegrey group-hover:text-sysbase group-active:text-sysbase">
                            <div class="flex justify-between w-full items-center py-[11px] hover:text-primary">
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
                                                :checked="(bool)$attachment->getSetting('play_once', $questionId)"
                                        />
                                    @endif
                                </div>
                            </div>
                            <div class="flex justify-between w-full items-center py-[11px] hover:text-primary">
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
                                                :checked="(bool)$attachment->getSetting('pausable', $questionId)"
                                        />
                                    @endif
                                </div>
                            </div>
                            <div @class([
                                "flex justify-between w-full items-center py-[5px]",
                                "hover:text-primary" => !$disableAudioTimer,
                                "hover:text-note text-note" => $disableAudioTimer,
                            ])>
                                <span class="text-base bold">{{ __('cms.Antwoordtijd') }}</span>
                                <div class="flex items-center relative">
                                    <x-tooltip class="mr-2">
                                <span class="text-base text-left">
                                    {{ __('cms.Antwoordtijd') }}
                                </span>
                                    </x-tooltip>
                                    @if($disableAudioTimer)
                                        <x-input.text
                                                type="number"
                                                maxlength="4"
                                                class="w-24 pr-10 text-note hover:text-note active:text-note"
                                                placeholder=""
                                                :disabled="true"
                                        />
                                    @elseif($upload)
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
                                                :value="$attachment->getSetting('timeout', $questionId)"
                                        />
                                    @endif
                                    <span class="audio-seconds-input"></span>
                                </div>
                            </div>
                        </div>
                        <div class="flex w-full h-px bg-blue-grey mb-2"></div>
                    @endif
                    <button class="flex items-center space-x-2 py-1 px-4 base hover:text-primary hover:bg-offwhite transition w-full"
                            @if($deleteAction)
                                @click="{{ $deleteAction }}"
                            @else
                                @click="$dispatch('delete-modal', ['{{ $upload ? 'upload' : 'attachment'}}', '{{ $upload ? $attachment->getFileName() : $attachment->uuid }}'])"
                            @endif

                    >
                        <x-icon.remove/>
                        <span class="text-base bold inherit">{{ __('cms.Verwijderen') }}</span>
                    </button>
                </div>
            @endif
        @endif


    </div>
</div>