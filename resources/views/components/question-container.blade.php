<div class="p-8 sm:p-10 content-section">
    <div class="question-title flex flex-wrap items-center question-indicator border-bottom mb-6">
        <div class="inline-flex question-number rounded-full text-center justify-center items-center complete">
            <span class="align-middle">{{ $mainQuestion->id }}</span>
        </div>
        <h1 class="inline-block ml-2 mr-6">{{ $mainQuestion->type }}</h1>
        <h4 class="inline-block">{{$mainQuestion->score}}pt</h4>
{{--        @if($mainQuestion->status = 'complete')--}}
{{--            <div class="ml-auto cta-primary">--}}
{{--                <x-icon.checkmark-small/>--}}
{{--                <span class="ml-auto note bold align-middle">BEANTWOORD</span>--}}
{{--            </div>--}}
{{--        @elseif($mainQuestion->status = 'pending')--}}
{{--            <div class="ml-auto text-gray-400">--}}
{{--                <x-icon.close/>--}}
{{--                <span class="ml-auto note bold align-middle">NIET BEANTWOORD </span>--}}
{{--            </div>--}}
{{--        @endif--}}
    </div>
    <div class="flex flex-wrap">
        {{ $slot }}
    </div>
</div>