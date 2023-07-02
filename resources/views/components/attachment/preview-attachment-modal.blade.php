@extends('components.attachment.attachment-modal')

@section('typeImplementations')
    @if($attachment)
        @if($this->attachmentType == 'video')
            <iframe class="w-full h-full" src="{{ $attachment->getVideoLink() }}"></iframe>
        @elseif($this->attachmentType == 'pdf')
            <iframe class="w-full h-full"
                    src="{{ route('teacher.preview.question-pdf-attachment-show', ['attachment' => $attachment->uuid, 'question' => $questionId], false) }}"></iframe>
        @elseif($this->attachmentType == 'audio')
            <x-attachment.preview-audio :attachment="$attachment" :questionId="$questionId" />
        @else
            <img src="{{ route('teacher.preview.question-attachment-show', ['attachment' => $attachment->uuid, 'question' => $questionId], false) }}"
                 selid="image-attachment"
                 alt="@lang('cms.bijlage')"
                 style="object-fit: contain; transition: opacity 150ms ease"
                 x-on:load="imageLoaded()"
                 :style="{'width': imageWidth, 'height': imageHeight}"
            />
        @endif
    @endif
@endsection