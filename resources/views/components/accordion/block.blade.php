<div x-data="accordionBlock(@js($key), @js($emitWhenSet))"
     id="accordion-block"
     role="region"
     @class([
        $attributes->get('class'),
        'accordion-block',
        'rounded-lg bg-white shadow relative' => $mode === 'panel',
        'transparent' => $mode === 'transparent',
        'accordion-disabled' => $disabled
        ])
     x-bind:class="{' bg-primary/5 border-dashed border-primary border-4 rounded-10 -m-1 ': droppingFile}"
        {{ $attributes->except(['class', 'key', 'emitWhenSet']) }}
>
    @if($upload)
        <div x-data="fileUpload(@js($uploadModel), @js($uploadRules))"
             x-on:drop="if(expanded) handleDrop()"
             x-on:drop.prevent="if(expanded) handleFileDrop($event)"
             x-on:dragover.prevent=""
             x-on:dragenter.prevent="if(expanded) handleDragEnter()"
             x-on:dragleave.prevent="if(expanded) handleDragLeave()"
        >
            @endif
            <div x-bind:id="id">
                <button
                        x-on:click="expanded = !expanded"
                        :aria-expanded="expanded"
                        @disabled($disabled)
                        @class([
                          'flex w-full items-center py-3 text-xl font-bold group transition-shadow',
                          'px-10 rounded-lg' => $mode === 'panel',
                          'border-b-3 border-sysbase' => $mode === 'transparent'
                          ])
                        @if($mode === 'panel')
                            x-on:mouseenter="if(!expanded) $el.classList.add('hover:shadow-hover')"
                        x-on:mouseleave="$el.classList.remove('hover:shadow-hover')"
                        @endif
                >
                    <div class="flex gap-4 items-center w-full flex-wrap">
                        {{ $title }}
                        {{ $titleLeft ?? '' }}
                    </div>
                    <div class="inline ml-auto">
                        <span x-bind:class="{'rotate-svg-90': expanded}"
                              x-bind:title="expanded ? $el.dataset.transCollapse : $el.dataset.transExpand"
                              @class([
                                'flex items-center justify-center rounded-full min-w-[40px] w-10 h-10 transition',
                                'group-hover:bg-primary/5' => !$disabled
                                ])
                                data-trans-collapse="@lang('general.inklappen')"
                                data-trans-expand="@lang('general.uitklappen')"
                        >
                            <svg @class(['transition','group-hover:text-primary' => !$disabled])  width="9" height="13"
                                 xmlns="http://www.w3.org/2000/svg">
                                <path class="stroke-current" stroke-width="3" d="M1.5 1.5l5 5-5 5" fill="none"
                                      fill-rule="evenodd"
                                      stroke-linecap="round" />
                            </svg>
                        </span>
                    </div>
                </button>
            </div>

            <div x-show="expanded"
                 x-collapse
            >
                <div @class(['pt-4 flex max-w-full', 'border-t-3 border-sysbase mx-10 pb-10' => $mode === 'panel'])>
                    {{ $body }}
                </div>
            </div>
            @if($coloredBorderClass)
                <div @class(['container-border-left', $coloredBorderClass])></div>
            @endif
            @if($upload)
        </div>
    @endif
</div>