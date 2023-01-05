<div>
    @php
        $counter = 0;
    @endphp
    @foreach($attachments as $index => $attachment)

        @switch($attachment->getFileType())
            @case('image')
            @case('image/jpg')
            @case('image/png')
                <div class="opgaven-image-container {{($loop->even) ? 'even-attachment' : 'odd-attachment'}} ">
                    <strong>{{ __('test-pdf.attachment') }} {{ $index + 1 }}: {{ __('test-pdf.image') }}</strong><br>
                    <i style="margin-bottom: 0.5rem">"{{ $attachment->title }}"</i> <br>
                </div>
                @break
            @case('audio')
                <div class="audio-container {{($loop->even) ? 'even-attachment' : 'odd-attachment'}}">
                    <strong>{{ __('test-pdf.attachment') }} {{ $index + 1 }}: {{ __('test-pdf.audio') }}</strong><br>
                    <i style="margin-bottom: 0.5rem">"{{ $attachment->title }}"</i> <br>
                </div>
                @break
            @case('video')
                <div class="video-container {{($loop->even) ? 'even-attachment' : 'odd-attachment'}}">
                    <strong>{{ __('test-pdf.attachment') }} {{ $index + 1 }}: {{ __('test-pdf.video') }}</strong><br>
                    @if($attachment->title)
                        <i>"{{ $attachment->title }}"</i> <br>
                    @endif
                    <span>{{ $attachment->link }}</span>
                </div>
                @break
            @case('pdf')
                <div class="pdf-container {{($loop->even) ? 'even-attachment' : 'odd-attachment'}}">
                    <strong>{{ __('test-pdf.attachment') }} {{ $index + 1 }}: {{ __('test-pdf.pdf') }}</strong><br>
                    <i style="margin-bottom: 0.5rem">"{{ $attachment->title }}"</i> <br>
                </div>
            @break
        @endswitch

    @endforeach
</div>
