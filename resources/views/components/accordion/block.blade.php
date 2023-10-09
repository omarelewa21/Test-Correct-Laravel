@props([
      'disabledBackGround' => false,
]
)
<div x-data="accordionBlock(@js($key), @js($emitWhenSet))"
     id="accordion-block"
     role="region"
     @if (!$disabledBackGround)
     @class([
        $attributes->get('class'),
        'accordion-block transition-shadow',
        'rounded-lg bg-white relative' => $mode === 'panel',
        'transparent' => $mode === 'transparent',
        'accordion-disabled' => $disabled
        ])
     @endif
     x-bind:class="{' bg-primary/5 border-dashed border-primary border-4 rounded-10 -m-1 ': droppingFile}"
     {{ $attributes->except(['class', 'key', 'emitWhenSet']) }}
     @if($mode === 'panel' && !$disabledBackGround)
        x-on:mouseenter="$el.classList.add('hover:shadow-hover')"
        x-on:mouseleave="$el.classList.remove('hover:shadow-hover')"
     @endif
     :data-block-id="id"
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
                        x-on:click="expanded = !expanded; $dispatch('accordion-toggled')"
                        :aria-expanded="expanded"
                        @disabled($disabled)
                        @class([
                          'flex w-full items-center py-3 text-xl font-bold group transition-shadow',
                          'rounded-lg' => $mode === 'panel',
                          'border-b-3 border-sysbase' => $mode === 'transparent',
                          ($disabledBackGround) ? 'px-0' : 'px-10',
                          ])

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
                            'group-hover:bg-primary/5 group-active:bg-primary/10 group-focus:bg-primary/5 group-focus:text-primary group-focus:border group-focus:border-[color:rgba(0,77,245,0.15)]' => !$disabled
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
                 block-body
            >
                <div @class(['accordion-content-slot | pt-4 flex max-w-full', 'border-t-3 border-sysbase pb-10' => $mode === 'panel', ($disabledBackGround) ? 'mx-0' : 'mx-10'] )>
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