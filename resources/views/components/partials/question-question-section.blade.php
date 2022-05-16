<x-upload.section uploadModel="uploads" :defaultFilepond="false" :multiple="true" :disableUpload="$this->isPartOfGroupQuestion">
    <x-slot name="files">
        <div id="attachment-badges" class="flex flex-wrap">
            @foreach($this->attachments as $attachment)
                <x-attachment.badge :upload="false"
                                    :attachment="$attachment"
                                    :title="$attachment->title"
                                    wire:key="a-badge-{{ $attachment->id }}"
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
                    />
                @elseif($video)
                    <x-attachment.video-badge
                            wire:key="video-{{ $key }}"
                            :video="$video"
                            :host="$this->getVideoHost($video['link'])"
                    />
                @endif
            @endforeach
            <x-attachment.dummy-badge model="uploads"/>
        </div>
    </x-slot>
    <x-slot name="filepond">
        <x-button.add-attachment>
            <x-slot name="text">
                <x-icon.attachment/>
                <span selid="add-attachment-btn">{!! __('cms.Bijlage toevoegen')  !!}</span>
            </x-slot>
        </x-button.add-attachment>
    </x-slot>

    <x-slot name="title">
        @if($this->obj instanceof \tcCore\Http\Livewire\Teacher\Questions\CmsGroup)
            {{ __('cms.bijlagen') }}
        @elseif($this->obj instanceof \tcCore\Http\Livewire\Teacher\Questions\CmsInfoScreen)
            {{ __('cms.Informatietekst') }}
        @else
            {{ __('cms.Vraagstelling') }}
        @endif
    </x-slot>
    @yield('question-cms-question')
</x-upload.section>
