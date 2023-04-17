<div class="drawer | right flex isolate overflow-hidden flex-shrink-0"
     x-data="assessmentDrawer(@js($inReview))"
     x-cloak
     x-bind:class="{'collapsed': collapse}"
     x-on:assessment-drawer-tab-update.window="tab($event.detail.tab)"
     x-on:resize.window.throttle="handleResize"
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
            <buttons
                    class="flex h-[60px] px-2 cursor-pointer items-center border-b-3 border-transparent hover:text-primary transition-colors"
                    x-on:click="tab(1)"
                    x-bind:class="activeTab === 1 ? 'primary border-primary hover:border-primary' : 'hover:border-primary/25'"
                    title="@lang('assessment.scoren')"
            >
                <x-icon.review />
            </buttons>
            <buttons
                    @class([
                                'flex h-[60px] px-2 cursor-pointer items-center border-b-3 border-transparent hover:text-primary transition-colors',
                                'text-midgrey pointer-events-none' => $feedbackTabDisabled,
                                ])
                    x-on:click="tab(2)"
                    x-bind:class="activeTab === 2 ? 'primary border-primary hover:border-primary' : 'hover:border-primary/25'"
                    title="@lang('assessment.Feedback')"
                    @disabled($feedbackTabDisabled)
            >
                <x-icon.feedback-text />
            </buttons>
            <buttons
                    @class([
                        'flex h-[60px] px-2 cursor-pointer items-center border-b-3 border-transparent hover:text-primary transition-colors',
                        'text-midgrey pointer-events-none' => !$coLearningEnabled
                        ])
                    x-on:click="tab(3)"
                    x-bind:class="activeTab === 3 ? 'primary border-primary hover:border-primary' : 'hover:border-primary/25'"
                    title="@lang($coLearningEnabled ? 'co-learning.co_learning' : 'assessment.CO-Learning no results')"
                    @disabled(!$coLearningEnabled)
            >
                <x-icon.co-learning />
            </buttons>
        </div>
        <div id="slide-container"
             class="slide-container | flex h-full max-w-[var(--sidebar-width)] overflow-x-hidden overflow-y-auto"
             wire:ignore.self
             x-on:scroll="closeTooltips()"
        >
            <div class="slide-1 scoring | p-6 flex-[1_0_100%] h-fit min-h-full w-[var(--sidebar-width)] space-y-4 isolate">
                <div class="flex-col w-full">
                    @if($group)
                        <div class="mb-2">
                            <div class="h-8 flex items-center">
                                <h5 class="inline-flex line-clamp-1"
                                    title="{!! $group->name !!}">{!! $group->name !!}</h5>
                            </div>
                            <div class="h-[3px] rounded-lg w-full bg-sysbase"></div>
                        </div>
                    @endif
                    <div class="question-indicator | items-center flex w-full">
                        <div class="inline-flex question-number rounded-full text-center justify-center items-center">
                            <span class="align-middle cursor-default">{{ $navigationValue }}</span>
                        </div>
                        <div class="flex gap-4 items-center relative top-0.5 w-full">
                            <h4 class="inline-flex"
                                selid="questiontitle">
                                <span>{{ $question->type_name }}</span>
                            </h4>
                            <h7 class="ml-auto inline-block">{{ $question->score }} pt</h7>
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