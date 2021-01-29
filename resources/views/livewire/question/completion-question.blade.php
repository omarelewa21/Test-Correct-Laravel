<x-partials.question-container :number="$number" :q="$q" :question="$question">
    <div class="w-full space-y-3">
        <div>
            <x-input.group class="body1" for="">
                {!! $html !!}
            </x-input.group>
        </div>
    </div>

    <x-attachment-modal :attachment="$attachment" />
</x-partials.question-container>
