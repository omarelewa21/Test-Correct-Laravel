<div {{ $attributes->class(['inline ml-auto']) }}>
    <span x-bind:class="{'rotate-svg-90': {{ $handler }}}"
          x-bind:title="{{ $handler }} ? $el.dataset.transCollapse : $el.dataset.transExpand"
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