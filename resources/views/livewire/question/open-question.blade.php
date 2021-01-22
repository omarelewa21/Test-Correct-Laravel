<div class="w-full">
        <div
                x-data="{count:0}"
                x-init="count = $refs.countme.value.length;"
                class="relative"
        >
            {!!   $question->getQuestionHtml() !!}

            <x-input.group for="me" label="" class="w-full">
                <x-input.textarea class="rounded-b-none" name="name" maxlength="280" x-ref="countme"
                                  x-on:keyup="count = $refs.countme.value.length"></x-input.textarea>
            </x-input.group>
            <div class="relative w-full border border-t-0 rounded-t-none border-blue-grey rounded-lg Z-10 overflow-hidden "
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


