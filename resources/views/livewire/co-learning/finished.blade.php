<div class="flex flex-col p-8 sm:p-10 content-section rs_readable relative">
    <div class="question-title flex flex-wrap items-center question-indicator border-bottom mb-6">
        <div class="inline-flex question-number rounded-full text-center justify-center items-center complete">
            <span class="align-middle cursor-default">
                <x-icon.checkmark/>
            </span>
        </div>

        {{--  <h4 class="inline-block ml-2"></h4> --}}

        <h1 class="inline-block ml-2 mr-6"
            selid="questiontitle"
        >
            {{ __('co-learning.finished_screen_title') }}
        </h1>


        <p class="ml-auto cta-primary flex space-x-2 items-center">
            <x-icon.checkmark-small/>
            <span class="ml-auto font-size-14 bold align-middle uppercase">
                {{ __('co-learning.finished') }}
            </span>
        </p>

    </div>

    <div class="flex flex-1 overview">

        <div class="questionContainer w-full">

            {{-- slot --}}
            <div class="w-full">
                <div class="relative">
                    <span>
                        {{ __('co-learning.finished_screen_text') }}
                    </span>
                </div>
                <div class="w-full flex justify-center items-center mt-12 mb-4">
                    <div class="w-[300px] h-[300px]">
                        <x-animations.co-learning-completed/>
                    </div>
                </div>
            </div>

        </div>
    </div>
    <div class="container-border-left student"></div>

    {{-- 'glass overlay' to prevent selecting text --}}
    {{-- <div x-on:contextmenu="$event.preventDefault()" class="absolute z-10 w-full h-full left-0 top-0"></div> --}}
</div>




