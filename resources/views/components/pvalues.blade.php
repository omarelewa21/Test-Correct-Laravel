@if ($pValue->getKey())
    <div class="border-b flex w-full items-center col-span-2 py-3"
        wire:key="pvalue-{{ $pValue->getKey() }}"
        wire:ignore
        x-data="{
            errorOpen: false,
            tooEasy: !!({{ $pValue->p_value }} > 0.8),
            tooHard: !!({{ $pValue->p_value }} < 0.2),
            error: null,
            orange: [1,7],
            yellow: [2,6],
            green: [3,5],
            darkgreen: [4],
            indicatorLeft: ({{ $pValue->p_value }} / 0.80 * 100) + 'px'
        }"
        x-init="error = (tooEasy || tooHard)"
    >
        <div class="flex items-center">
            <span class="bold text-base">{{ __('cms.p-waarde') }} {{ $pValue->education_level_year }} {{ optional($pValue->educationLevel)->name }}</span>
        </div>
        <div class="flex items-center px-10 space-x-12">
            <span class="text-base">{!! number_format( $pValue->p_value, 2) !!}</span>
            <div class="flex w-[22px] h-[22px] relative">
                <span x-show="error"
                    x-cloak
                    x-transition
                    class="flex w-full h-full rounded-full bg-allred text-white items-center justify-center cursor-pointer"
                    :class="{'main-shadow': errorOpen}"
                    @click="errorOpen = !errorOpen"
                >
                    <span class="flex" x-show="!errorOpen" x-transition>
                        <x-icon.exclamation/>
                    </span>
                    <span class="flex" x-show="errorOpen" x-transition>
                        <x-icon.close-small/>
                    </span>
                </span>
                <div x-show="errorOpen"
                    x-cloak
                    x-transition:enter="-translate-x-1/2"
                    @click.outside="errorOpen = false"
                    class="absolute bg-off-white rounded-10 w-[350px] p-5 z-10 left-1/2 -translate-x-1/2 top-8 main-shadow"
                >
                    <template x-if="tooEasy">
                        <span class="text-base">{{ __('cms.pvalue_too_easy') }}</span>
                    </template>
                    <template x-if="tooHard">
                        <span class="text-base">{{ __('cms.pvalue_too_hard') }}</span>
                    </template>
                    <x-button.text-button type="link" href="{{ __('cms.pvalue_support_link') }}" target="_blank">
                        <span class="text-base">{{ __('cms.Lees meer hierover op de Kennisbank') }}</span>
                        <x-icon.arrow/>
                    </x-button.text-button>
                </div>
            </div>
            <div class="flex relative">
                <div class="flex rounded-10 overflow-hidden">
                    <template x-for="i in 7">
                        <span class="w-[22px] h-2.5 bg-allred rounded-sm"
                            :class="{
                                'bg-orange': orange.includes(i),
                                'bg-lightgreen': green.includes(i),
                                'bg-student': yellow.includes(i),
                                'bg-ctamiddark': darkgreen.includes(i),
                                'mr-1': i !== 7
                                }"
                        ></span>
                    </template>
                </div>
                <span class="text-xs text-midgrey absolute top-3 left-0">0.00</span>
                <span class="text-xs text-midgrey absolute top-3 right-0">0.80</span>
                <span class="pvalue-indicator" :style="'left:' + indicatorLeft"></span>
            </div>
        </div>

        <div class="flex items-center">
            <span class="inline-flex text-base">
                {{ $pValue->p_value_count }} {{ __("cms.keer afgenomen") }}
            </span>
        </div>
    </div>    
@endif