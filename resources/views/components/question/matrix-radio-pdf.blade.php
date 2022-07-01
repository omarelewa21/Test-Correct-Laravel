@props([
'subQuestionId',
'questionAnswerId',
'disabled' => false,
])
@php
    $showCheckmark = false;
    if (isset($this->answerStruct[$subQuestionId]) && $this->answerStruct[$subQuestionId] == $questionAnswerId) {
        $showCheckmark = true;
    }
@endphp


<div id="matrix_radio_{{ $subQuestionId }}_{{ $questionAnswerId }}"
     class="flex w-5 h-5 cursor-pointer rounded-full bg-white items-center justify-center transition border border-primary-hover
            @if($showCheckmark) border-primary @else border-system-secondary @endif">
    @if($showCheckmark)
        <x-icon.checkmark-pdf id="matrix_radio_checkmark{{ $subQuestionId }}_{{ $questionAnswerId }}" class="primary no-margin"></x-icon.checkmark-pdf>
    @endif
</div>

