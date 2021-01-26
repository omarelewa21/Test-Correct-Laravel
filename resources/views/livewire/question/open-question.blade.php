<div class="flex flex-col p-8 sm:p-10 content-section"  x-data="{ showMe: false }" x-on:current-updated.window="showMe = ({{ $number }} == $event.detail.current)"  x-show="showMe"  >
    <div class="question-title flex flex-wrap items-center question-indicator border-bottom mb-6">
        <div class="inline-flex question-number rounded-full text-center justify-center items-center complete">
            <span class="align-middle">{{ $number }}</span>
        </div>
        <h1 class="inline-block ml-2 mr-6">{!!  __($question->caption) !!}</h1>
        <h4 class="inline-block">{{ $question->score }} pt</h4>
    </div>
    <div class="w-full">
        <div
            x-data="{count:0}"
            x-init="count = $refs.countme.value.length;"
            class="relative"
        >
            {!!   $question->getQuestionHtml() !!}


            <x-input.group for="me" label="{!! __('test_take.instruction_open_question') !!}" class="w-full mt-3 relative primary">
                <x-input.textarea
                    wire:key="textarea_{{ $question->id }}"
                    class=""
                    style="min-height:80px "
                    name="name"
                    maxlength="140"
                    x-ref="countme"
                    wire:model="answer"
                    x-on:keyup="count = $refs.countme.value.length"
                ></x-input.textarea>
                <div
                    class="absolute bottom-0 w-full bg-blue-grey rounded-lg Z-10 overflow-hidden "
                    style="height: 10px;">
                <span :style="calculateProgress(count, $refs.countme.maxLength)"
                      class="transition bg-primary absolute h-2 border border-primary rounded-lg"></span>
                </div>
            </x-input.group>

            <div class="mt-1 primary text-sm bold">
                <span x-html="count"></span> / <span x-html="$refs.countme.maxLength"></span> <span>{!! __('test_take.characters') !!}</span>
            </div>
        </div>

        <script>
            function calculateProgress(count, total) {
                return 'height: 10px; width:' + count / total * 100 + '%';
            }
        </script>
    </div>
</div>

