<x-partials.overview-question-container :number="$number" :question="$question" :answer="$answer">

    <div class="w-full space-y-3" x-data="completionQuestion()"
         x-init="$el.querySelectorAll('input')
                .forEach(function(el){
                    if(el.value == '') {
                        el.classList.add('border-red')
                    }
                 })
             $el.querySelectorAll('select')
                .forEach(function(el){
                    if(el.value == '') {
                        el.classList.add('border-red')
                    }
                 });
                 setTitlesOnLoad($el);
                 ">

        <div class="completion-question-overview-container">
            @if($this->question->isSubType('multi'))
                <div class="flex flex-wrap items-center">
                    @foreach($questionTextPartials as $answerIndex => $textPartialArray)
                        @foreach($textPartialArray as $textPartial){{--
                        --}}{!!$textPartial!!}{{-- Do not format this file. It causes unfixable/unwanted whitespaces.
                    --}}@endforeach
                        <x-input.select class="!w-fit mb-1 mr-1 text-base"
                                        wire:model="answer.{{ $answerIndex + 1 }}"
                                        :error="empty($this->answer[$answerIndex + 1])"
                        >
                            @foreach($options[$answerIndex + 1] as $key => $option)
                                <x-input.option :value="$option" :label="$option" />
                            @endforeach
                        </x-input.select>
                    @endforeach
                    @foreach($questionTextPartialFinal as $textPartial){{--
                    --}}{!!$textPartial!!}{{--
                 --}}@endforeach
                </div>
            @else
                <x-completion-question-converted-html :question="$this->question"/>
            @endif
        </div>
    </div>
    <x-attachment.attachment-modal :attachment="$attachment" :answerId="$answerId"/>
</x-partials.overview-question-container>
