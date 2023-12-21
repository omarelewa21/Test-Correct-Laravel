@props(['withUpload' => false])
@php($disableAudioTimer = ($this->obj instanceof \tcCore\Http\Livewire\Teacher\Cms\Providers\Group || $this->obj instanceof \tcCore\Http\Livewire\Teacher\Cms\Providers\InfoScreen))
<x-accordion.container active-container-key="question-section"
                       :wire:key="'question-section-'.$this->uniqueQuestionKey"
>
    <x-accordion.block key="question-section"
                       :upload="$withUpload"
                       uploadModel="uploads"
                       :uploadRules="$this->uploadRules ?? []"
                       :wire:key="'question-block-'.$this->uniqueQuestionKey"
    >
        <x-slot:title>
            <h4>{{ $this->questionSectionTitle() }}</h4>
        </x-slot:title>
        <x-slot:body>
            <div x-data="{attachmentOverlay: false}"
                 class="upload-section w-full flex flex-col"
            >

                <div class="flex mb-4 flex-wrap relative">
                    <div class="inline-flex flex-wrap" id="upload-dummies" wire:ignore></div>
                    <div id="attachment-badges" class="flex flex-wrap"
                         wire:key="attachment-section-{{$this->uniqueQuestionKey}}"
                    >
                        @foreach($this->attachments as $attachment)
                            <x-attachment.badge :upload="false"
                                                :attachment="$attachment"
                                                :title="$attachment->title"
                                                wire:key="a-badge-{{ $attachment->id.$this->questionEditorId }}"
                                                :disabled="isset($this->isPreview)"
                                                :disable-audio-timer="$disableAudioTimer"
                            />
                        @endforeach

                        @foreach($this->sortOrderAttachments as $key => $item)
                            @php([$upload, $video] = $this->getUploadOrVideo($item))

                            @if($upload)
                                <x-attachment.badge
                                        wire:key="upload-{{ $key }}"
                                        :upload="true"
                                        :attachment="$upload"
                                        :title="$upload->getClientOriginalName()"
                                        :disabled="isset($this->isPreview)"
                                        :disable-audio-timer="$disableAudioTimer"
                                />
                            @elseif($video)
                                <x-attachment.video-badge
                                        wire:key="video-{{ $key }}"
                                        :video="$video"
                                        :host="$this->getVideoHost($video['link'])"
                                        :disabled="isset($this->isPreview)"
                                />
                            @endif
                        @endforeach
                        {{--                        <x-attachment.dummy-badge model="uploads" />--}}
                    </div>
                    <div>
                        <x-input.filepond wire:model="uploads"
                                          class="flex space-x-4 flex-wrap"
                                          :multiple="true"
                                          :showDefault="false"
                        >
                            <x-button.add-attachment :disabled="isset($this->isPreview)">
                                <x-slot name="text">
                                    <x-icon.attachment />
                                    <span selid="add-attachment-btn">{!! __('cms.Bijlage toevoegen')  !!}</span>
                                </x-slot>
                            </x-button.add-attachment>
                        </x-input.filepond>
                        @error('uploads')
                        <span class="text-base all-red">{{ __('cms.file type not allowed') }}</span>
                        @enderror
                    </div>
                    <div class="flex flex-1 p-4 absolute -inset-2.5 rounded-10 bg-secondary/30 items-center justify-center"
                         @filepond-start.window="attachmentOverlay = true;"
                         @filepond-finished.window="attachmentOverlay = false;"
                         x-show="attachmentOverlay"
                         x-transition
                         x-cloak
                    >
                        <div class="flex  bg-white/95 rounded-md px-4 py-1">
                            <span class="">{{ __('cms.one_moment_please') }} {{ __('cms.attachment_uploading_message') }}</span>

                        </div>
                    </div>
                </div>

                @yield('question-cms-question')
            </div>


            <template id="upload-badge">
                <div class="badge inline-flex relative border rounded-lg border-blue-grey items-center mr-4 mb-2 overflow-hidden"
                     wire:ignore>
                    <div class="flex p-2 border-r border-blue-grey h-full items-center">
                        <x-icon.attachment />
                    </div>
                    <div class="flex base items-center relative">
                        <span class="badge-name p-2 note italic max-w-[236px] truncate"></span>
                    </div>
                    <div class="absolute bg-bluegrey h-1.5 w-full bottom-0">
                        <div
                                class="bg-primary h-1.5"
                                style="transition: width 1s"
                                :style="`width: ${progress[$el.closest('.badge').id]}%;`"
                        >
                        </div>
                    </div>
                </div>
            </template>
        </x-slot:body>
    </x-accordion.block>
</x-accordion.container>



