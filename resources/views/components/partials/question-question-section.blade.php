<x-upload.section uploadModel="uploads" :defaultFilepond="false" :multiple="true"
                  :enableUpload="!isset($this->isPreview)">
    <x-slot name="files">
        <div id="attachment-badges" class="flex flex-wrap" wire:key="attachment-section-{{$this->uniqueQuestionKey}}">
            @foreach($this->attachments as $attachment)
                <x-attachment.badge :upload="false"
                                    :attachment="$attachment"
                                    :title="$attachment->title"
                                    wire:key="a-badge-{{ $attachment->id.$this->questionEditorId }}"
                                    :disabled="isset($this->isPreview)"
                />
            @endforeach

            @foreach($this->sortOrderAttachments as $key => $item)
                @php
                    list($upload, $video) = $this->getUploadOrVideo($item);
                @endphp

                @if($upload)
                    <x-attachment.badge
                            wire:key="upload-{{ $key }}"
                            :upload="true"
                            :attachment="$upload"
                            :title="$upload->getClientOriginalName()"
                            :disabled="isset($this->isPreview)"
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
            <x-attachment.dummy-badge model="uploads" />
        </div>
    </x-slot>
    <x-slot name="filepond">
        <x-button.add-attachment :disabled="isset($this->isPreview)">
            <x-slot name="text">
                <x-icon.attachment />
                <span selid="add-attachment-btn">{!! __('cms.Bijlage toevoegen')  !!}</span>
            </x-slot>
        </x-button.add-attachment>
    </x-slot>

    <x-slot name="title">
        {{ $this->questionSectionTitle() }}
    </x-slot>
    @yield('question-cms-question')
</x-upload.section>
