<div x-data="{active: 2}" {{ $attributes->merge(['class' =>' text-toggle inline-flex border border-secondary bg-offwhite relative rounded-lg']) }}>
    <span @click="active = 1" class="px-4 py-2 bold note cursor-default"
          :class="{'primary': active === 1}">{{ $first }}</span>
    <span @click="active = 2" class="px-4 py-2 bold" :class="{'primary': active === 2}">{{ $second }}</span>

    <span class="active-border absolute -inset-px border-2 border-primary rounded-lg transition-all"
          :style="active === 1 ? 'left:0' : 'left:'+ $el.offsetWidth +'px' "
    ></span>
</div>