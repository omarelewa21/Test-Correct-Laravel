<x-partials.answer-model-question-container :number="$number" :question="$question" :answer="$answer">

    <div class="w-full space-y-3 question-no-break-completion" x-data="">
        <div>
            <x-input.group class="body1 max-w-full flex-col children-block-pdf" for="" x-data="">
                {!! $html  !!}
            </x-input.group>
        </div>
    </div>
</x-partials.answer-model-question-container>
