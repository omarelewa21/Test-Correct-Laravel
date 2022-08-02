<div>
    @foreach($attachments as $attachment)
        <div class="attachment-container" style="margin-bottom: 1rem; display: inline-block">
            @switch($attachment->getFileType())
                @case('image')
                @case('image/jpg')
                @case('image/png')
                    <div class="image-container">
                        <strong>{{ __('test-pdf.image')  }} 1</strong>
                        <div>
                            <img src="{{ $attachment->getCurrentPath() }}" alt="{{ $attachment->title }}"/>
                            <div class="italic" style="font-align: center; padding-left: 1rem;">
                                {{ $attachment->title }}
                            </div>
                        </div>
                    </div>
                    @break
                @case('audio')
                    <div class="audio-container">
                        <strong>{{ __('test-pdf.audio') }}</strong><br>
                        <i>"{{ $attachment->title }}"</i> <br>
                        <span >{{ __('test-pdf.audio_text') }}</span>
                    </div>
                    @break
                @case('video')
                    <div class="video-container">
                        <strong>{{ __('test-pdf.video') }}</strong> <br>
                        {{ $attachment->link }}
                    </div>

                    @break
            @endswitch
        </div>
    @endforeach
</div>
