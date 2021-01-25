<div class="flex flex-col p-8 sm:p-10 content-section"  >
    <div class="question-title flex flex-wrap items-center question-indicator border-bottom mb-6">
        <div class="inline-flex question-number rounded-full text-center justify-center items-center {!! $answer? 'complete': 'incomplete' !!}">
            <span class="align-middle">{{ $number }}</span>
        </div>
        <h1 class="inline-block ml-2 mr-6">{!!  __($question->caption) !!}</h1>
        <h4 class="inline-block">{{ $question->score }} pt</h4>
        @if ($this->answer)
            <x-answered></x-answered>
        @else
            <x-not-answered></x-not-answered>
        @endif
    </div>
    <div class="w-full">
        <div
            x-data="{count:0}"
            x-init="count = $refs.countme.value.length;"
            class="relative"
        >
            {!!   $question->getQuestionHtml() !!}

            <x-input.group for="me" label="" class="w-full">
                <x-input.textarea
                    wire:key="textarea_{{ $question->id }}"
                    class="rounded-b-none"
                    name="name"
                    maxlength="280"
                    x-ref="countme"
                    wire:model="answer"
                    x-on:keyup="count = $refs.countme.value.length"
                ></x-input.textarea>
            </x-input.group>
            <div
                class="relative w-full border border-t-0 rounded-t-none border-blue-grey rounded-lg Z-10 overflow-hidden "
                style="height: 25px;">
                <span :style="calculateProgress(count, $refs.countme.maxLength)"
                      class="transition bg-primary absolute h-6 rounded-t-none rounded-br-none rounded-lg"></span>
            </div>
            <div class="mt-1">
                <span x-html="count"></span> / <span x-html="$refs.countme.maxLength"></span>
            </div>
        </div>

        <script>
            function calculateProgress(count, total) {
                return 'width:' + count / total * 100 + '%';
            }
        </script>
    </div>
</div>

