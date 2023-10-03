<div class="drawer | right flex isolate overflow-hidden flex-shrink-0"
     x-data="assessmentDrawer(@js($inReview))"
     x-cloak
     x-bind:class="{'collapsed': collapse}"
     x-on:answer-feedback-drawer-tab-update.window="tab($event.detail.tab, true, $event.detail?.uuid)"
     x-on:continue-navigation="Alpine.$data($el)[$event.detail.method]()"
     x-on:resize.window.throttle="handleResize"
     wire:key="evaluation-drawer-{{ $uniqueKey }}"
>
    <div class="collapse-toggle vertical white z-10 cursor-pointer"
         @click="collapse = !collapse;"
    >
        <button class="relative"
                :class="{'rotate-svg-180 -left-px': collapse}"
        >
            <x-icon.chevron class="-top-px relative" />
        </button>
    </div>

    <div class="flex flex-1 flex-col sticky top-[var(--header-height)]">
        <div class="flex w-full justify-center gap-2 z-1"
             style="box-shadow: 0 3px 8px 0 rgba(4, 31, 116, 0.2);">
            <button
                    class="flex h-[60px] px-2 cursor-pointer items-center border-b-3 border-transparent hover:text-primary hover:bg-primary/5 active:text-primary active:bg-primary/10 transition-colors"
                    x-on:click="tab(1)"
                    x-bind:class="{'primary border-primary': activeTab === 1}"
                    title="@lang('assessment.scoren')"
            >
                <x-icon.review />
            </button>
            <button
                    @class([
                            'flex h-[60px] px-2 items-center border-b-3 border-transparent transition-colors',
                            'text-midgrey cursor-default' => $feedbackTabDisabled,
                            'hover:text-primary hover:bg-primary/5 active:text-primary active:bg-primary/10 cursor-pointer' => !$feedbackTabDisabled,
                                ])
                    @if(!$feedbackTabDisabled)
                        x-on:click="openFeedbackTab()"
                        x-bind:class="{'primary border-primary': activeTab === 2}"
                    @endif
                    title="@lang('assessment.Feedback')"
                    @disabled($feedbackTabDisabled)
            >
                <x-icon.feedback-text />
            </button>
            <button
                    @class([
                            'flex h-[60px] px-2 items-center border-b-3 border-transparent transition-colors',
                            'text-midgrey cursor-default' => !$coLearningEnabled,
                            'hover:text-primary hover:bg-primary/5 active:text-primary active:bg-primary/10 cursor-pointer' => $coLearningEnabled
                        ])
                    @if($coLearningEnabled)
                        x-on:click="tab(3)"
                        x-bind:class="{'primary border-primary': activeTab === 3}"
                    @endif
                    title="@lang($coLearningEnabled ? 'co-learning.co_learning' : 'assessment.CO-Learning no results')"
                    @disabled(!$coLearningEnabled)
            >
                <x-icon.co-learning />
            </button>
        </div>
        <div id="slide-container"
             class="slide-container | flex h-full max-w-[var(--sidebar-width)] overflow-x-hidden overflow-y-auto hide-scrollbar"
             wire:ignore.self
             wire:key="slide-container-{{ $uniqueKey }}"
             x-on:scroll="closeTooltips()"
        >
            <div class="slide-1 scoring | p-6 flex-[1_0_100%] h-fit min-h-full w-[var(--sidebar-width)] space-y-4 isolate">
                <div class="flex-col w-full">
                    @if($group)
                        <div class="mb-2">
                            <div class="h-8 flex items-center">
                                <h5 class="inline-flex max-w-[252px]"
                                    title="{!! $group->name !!}">
                                    <span class=" truncate">{!! $group->name !!}</span>
                                </h5>
                            </div>
                            <div class="h-[3px] rounded-lg w-full bg-sysbase"></div>
                        </div>
                    @endif
                    <div class="question-indicator | items-center flex w-full">
                        <div class="inline-flex question-number rounded-full text-center justify-center items-center">
                            <span class="align-middle cursor-default">{{ $navigationValue }}</span>
                        </div>
                        <div class="flex gap-2 items-center relative top-0.5 w-full">
                            <h4 class="inline-flex"
                                selid="questiontitle">
                                <span>{{ $question->type_name }}</span>
                            </h4>
                            @if($inReview)
                            <div class="ml-auto flex min-w-fit">
                                <h7 class="inline-block">{{  $score ?? '-' }}</h7>
                                <span>/</span>
                                <span class="body2">{{ $question->score }} pt</span>
                            </div>
                            @else
                            <h7 class="ml-auto inline-block">{{ $question->score }} pt</h7>
                            @endif
                        </div>
                    </div>
                </div>

                {{ $slideOneContent }}
            </div>
            <div class="slide-2 feedback | p-6 flex-[1_0_100%] h-fit min-h-full w-[var(--sidebar-width)] space-y-4 isolate">
                <div class="flex flex-col w-full gap-2">

                    {{ $slideTwoContent }}

                </div>
            </div>
            <div class="slide-3 co-learning | p-6 flex-[1_0_100%] h-fit min-h-full w-[var(--sidebar-width)] space-y-4">
                <div class="flex flex-col w-full gap-2">
                    {{ $slideThreeContent }}
                </div>
            </div>
        </div>
        <div class="nav-buttons | flex w-full justify-between items-center gap-2 px-6 h-[var(--header-height)] "
             style="box-shadow: 0 -3px 8px 0 rgba(77, 87, 143, 0.3);"
             wire:key="drawer-nav-buttons-{{ $uniqueKey }}"
        >
            {{ $buttons }}
        </div>
    </div>
</div>