<div class="w-full">
    @if($question->subtype == 'short')
        <div
                x-data="{count:0}"
                x-init="count = $refs.countme.value.length"
                x-on:livewire-refresh.window="count = $refs.countme.value.length;"
                class="relative"
        >
            {!!   $question->getQuestionHtml() !!}

            <x-input.group for="me" label="" class="w-full">
                <x-input.textarea wire:model="answer" name="name" maxlength="280" x-ref="countme"
                                  x-on:keyup="count = $refs.countme.value.length"></x-input.textarea>
            </x-input.group>
            <div class="relative z-20 ml-4 mt-3">
                <span x-html="count"></span> / <span x-html="$refs.countme.maxLength"></span>
            </div>
            <div class="absolute w-full border border-blue-grey rounded-lg left-0 Z-10 overflow-hidden "
                 style="height: 26px; bottom: -1px;">
                <span :style="calculateProgress(count, $refs.countme.maxLength)"
                      class="transition bg-blue-300 bottom-0 absolute h-6 rounded-lg"></span>
            </div>
        </div>
    @else
        <div class="w-full">
            <x-input.group for="me" label="" class="w-full">
                <x-input.textarea wire:model="answer" name="name"></x-input.textarea>
            </x-input.group>

        </div>
    @endif

    <script>
        function calculateProgress(count, total) {
            return 'width:' + count / total * 100 + '%';
        }
    </script>
</div>


