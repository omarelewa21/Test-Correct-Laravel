<div>
    @php
        $counter = 0;
    @endphp
    @foreach($attachments as $attachment)

        @switch($attachment->getFileType())
            @case('image')
            @case('image/jpg')
            @case('image/png')

                <div class="image-container {{(++$counter % 2 == 0) ? 'even-image-attachment' : 'odd-image-attachment'}} ">
                    <strong>{{ __('test-pdf.image')  }} {{ $attachment_counters['image'][$attachment->getKey()] ?? '' }}</strong>
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
                    <strong>{{ __('test-pdf.audio') }} {{ $attachment_counters['audio'][$attachment->getKey()] ?? '' }}</strong><br>
                    <i>"{{ $attachment->title }}"</i> <br>
                    <span>{{ __('test-pdf.audio_text') }}</span>
                </div>
                @break
            @case('video')
                <div class="video-container">
                    <strong>{{ __('test-pdf.video') }} {{ $attachment_counters['video'][$attachment->getKey()] ?? '' }}</strong> <br>
                    {{ $attachment->link }}
                </div>
                @break
        @endswitch

    @endforeach
</div>
