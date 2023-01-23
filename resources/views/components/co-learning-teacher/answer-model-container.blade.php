@props([
'question',
])

<div class="flex flex-col pt-[14px] pb-[33px] px-10 content-section rs_readable relative"
     x-data="{collapsed: true}"
>
    <div class="question-title flex flex-wrap items-center question-indicator border-bottom mb-2">

        <h4 class="inline-block mr-6"
            selid="questiontitle"> {{ __('co-learning.answer_model') }}</h4>
        <div class="absolute right-[-14px] group" @click="collapsed = ! collapsed">
            <div class="w-10 h-10 rounded-full flex items-center justify-center group-hover:bg-primary group-hover:opacity-[0.05]"></div>
            <template x-if="true">
                <x-icon.chevron class="absolute top-[14px] left-4 text-sysbase transition" x-bind:class="collapsed ? '' : 'rotate-90'" x-cloak/>
            </template>
        </div>
    </div>

    <div x-show="!collapsed" x-collapse.duration.500ms x-cloak>

            <div class="questionContainer w-full">
                <div class="w-full">
                    <div class="relative">
                        <x-input.group for="me" class="w-full disabled mt-4">
                            <div class="border border-light-grey p-4 rounded-10 h-fit">
                                {!! $question->answer !!}
                            </div>
                        </x-input.group>
                    </div>
                </div>
            </div>

    </div>
    <div class="container-border-left-primary"></div>
</div>

