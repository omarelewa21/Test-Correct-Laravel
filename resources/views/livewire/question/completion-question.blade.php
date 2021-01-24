<div class="flex flex-col p-8 sm:p-10 content-section" x-show="'{{ $number }}' == current">
    <div class="question-title flex flex-wrap items-center question-indicator border-bottom mb-6">
        <div class="inline-flex question-number rounded-full text-center justify-center items-center complete">
            <span class="align-middle">{{ $number }}</span>
        </div>
        <h1 class="inline-block ml-2 mr-6">{!! __($question->caption) !!}</h1>
        <h4 class="inline-block">{{ $number }} pt</h4>
    </div>

    <div class="flex flex-1">
        <div class="w-full space-y-3">
            <div>
                <x-input.group class="body1" for="">
                    {!! $html !!}
                </x-input.group>
            </div>
        </div>
    </div>
</div>
