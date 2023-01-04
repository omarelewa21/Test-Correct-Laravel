<div>
    @php
        $counter = 0;
    @endphp
    @foreach($attachments as $index => $attachment)

        @switch($attachment->getFileType())
            @case('image')
            @case('image/jpg')
            @case('image/png')

                <div class="image-container {{($loop->even) ? 'even-attachment' : 'odd-attachment'}} ">
{{--                    <strong>{{ __('test-pdf.image')  }} {{ $attachment_counters['image'][$attachment->getKey()] ?? '' }}</strong>--}}
                    <strong>{{ __('test-pdf.attachment') }} {{ $index + 1 }}: {{ __('test-pdf.image') }}</strong>
                    <div>
                        <img src="{{ $attachment->getCurrentPath() }}" alt="{{ $attachment->title }}"/>
                        <div class="italic" style="font-align: center; padding-left: 1rem;">
                            {{ $attachment->title }}
                        </div>
                    </div>
                </div>
                @break
            @case('audio')
                <div class="audio-container {{($loop->even) ? 'even-attachment' : 'odd-attachment'}}">
{{--                    <strong>{{ __('test-pdf.audio') }} {{ $attachment_counters['audio'][$attachment->getKey()] ?? '' }}</strong><br>--}}
                    <strong>{{ __('test-pdf.attachment') }} {{ $index + 1 }}: {{ __('test-pdf.audio') }}</strong><br>
                    <i style="margin-bottom: 0.5rem">"{{ $attachment->title }}"</i> <br>
{{--                    <span>{{ __('test-pdf.audio_text') }}</span>--}}
                </div>
                @break
            @case('video')
                <div class="video-container {{($loop->even) ? 'even-attachment' : 'odd-attachment'}}">
{{--                    <strong>{{ __('test-pdf.video') }} {{ $attachment_counters['video'][$attachment->getKey()] ?? '' }}</strong> <br>--}}
                    <strong>{{ __('test-pdf.attachment') }} {{ $index + 1 }}: {{ __('test-pdf.video') }}</strong> <br>
                    @if($attachment->title)
                        <i>"{{ $attachment->title }}"</i> <br>
                    @endif
                    <span>{{ $attachment->link }}</span>
                </div>
                @break
            @case('pdf')
                <div class="pdf-container {{($loop->even) ? 'even-attachment' : 'odd-attachment'}}">
{{--                    <strong>{{ __('test-pdf.pdf') }} {{ $attachment_counters['pdf'][$attachment->getKey()] ?? '' }}</strong><br>--}}
                    <strong>{{ __('test-pdf.attachment') }} {{ $index + 1 }}: {{ __('test-pdf.pdf') }}</strong><br>
                    <i style="margin-bottom: 0.5rem">"{{ $attachment->title }}"</i> <br>
{{--                    <span>{{ __('test-pdf.pdf_text') }}</span>--}}
                </div>
            @break
        @endswitch

    @endforeach
</div>
