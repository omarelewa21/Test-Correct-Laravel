<div x-data="{
        isOpen: false,
        colorSelected: '#000000',
        showX: false,
        colors: [
            '#16465a', '#002f7a', '#240075', '#3e005c', '#52001c', '#6a1500',  '#592500', '#5d4500', '#646200', '#334400', '#164401', '#00341d',
            '#265f76', '#003995', '#3300a8', '#55007f', '#700027', '#8d1c00',  '#783200', '#7d5d00', '#848100', '#425800', '#1e5f00', '#004f2c',
            '#3a7f9e', '#0047ba', '#3f00d0', '#6e00a3', '#8f0031', '#ae2300',  '#963f00', '#977100', '#a5a200', '#5e7e00', '#2c8b01', '#1a6141',
            '#4a9cbe', '#0055de', '#4d00ff', '#8800cb', '#ad003c', '#bc3700',  '#ce5600', '#c5a200', '#c8c400', '#7ea900', '#3cbd02', '#009c56',
            '#56afdc', '#0061ff', '#6c2dff', '#ab01ff', '#cf0048', '#d94000',  '#e9741f', '#deb600', '#e2de00', '#97c805', '#4ddb0d', '#06c670',
            '#68cff9', '#1a71ff', '#8854ff', '#c349ff', '#ff0058', '#ff5f1c',  '#ff9e1c', '#ffd200', '#fffa00', '#b1e90b', '#61ed23', '#23de8b',
            '#88dbfa', '#4a8fff', '#a178ff', '#d57fff', '#ff5e95', '#ff8552',  '#ffb200', '#ffe362', '#fffc5f', '#d9ff00', '#8bfc58', '#46f6a8',
            '#b3e7fb', '#79acff', '#bb9eff', '#e3aaff', '#ff9dbf', '#ffaa86',  '#ffc92a', '#fff1b1', '#fffd9c', '#eaff71', '#b9ff99', '#8effcc',
            '#daf2fd', '#b2cfff', '#d4c1ff', '#ecc5ff', '#ffcdde', '#ffd2bf',  '#ffe28e', '#fffae2', '#fffedd', '#f2fbc1', '#dcffcd', '#d5ffec',
            '#000000', '#222222', '#454545', '#616161', '#808080', '#9a9a9a',  '#bebebe', '#d6d6d6', '#ebebeb', '#f1f1f1', '#f8f8f8', '#ffffff',
        ],
        triggerInputEvent: function () {
            setTimeout(() => {
                $el.querySelector('input').dispatchEvent(new Event('input'))
            }, 100)
        }
    }"
    x-cloak
    x-effect="$refs.canvas.style.pointerEvents = isOpen ? 'none' : 'initial'"
>
    <div class="max-w-sm">
        <div class="mb-5">
            <div class="flex items-center">
                <div class="relative ml-3 mt-8">
                <!-- Selector Input -->
                    <div
                        title="{{$attributes->get('title')}}"
                        class="color-pallete cursor-pointer bg-white @if($isPenColorInput()) add-border @endif"
                        @click="isOpen = !isOpen"
                        @mouseover="showX = true"
                        @mouseleave="showX = false"
                        :class="isOpen && 'outline'"
                    >
                        <input
                            id="{{$name}}"
                            name="{{$name}}"
                            autocomplete="off"
                            class="cursor-pointer"
                            type="button"
                            :value="`${colorSelected}`"
                            @if($isStrokeColorInput())
                                :style="`border-color: ${colorSelected}`"
                            @else
                                :style="`background: ${colorSelected}; color: ${colorSelected};`"
                            @endif
                        >

                            <svg
                                x-show="isOpen && showX"
                                class="pallete-x-button inline-block absolute"
                                width="14" height="14" xmlns="http://www.w3.org/2000/svg"
                            >
                                <g class="stroke-current" fill="none" fill-rule="evenodd" stroke-linecap="round" stroke-width="3">
                                    <path d="M1.5 12.5l11-11M12.5 12.5l-11-11"></path>
                                </g>
                            </svg>
                    </div>

                <!-- Color Palette Container  -->
                    <div 
                        x-show="isOpen" 
                        @click.away="isOpen = false"
                        x-transition:enter="transition ease-out duration-100 transform"
                        x-transition:enter-start="opacity-0 scale-95"
                        x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-75 transform"
                        x-transition:leave-start="opacity-100 scale-100"
                        x-transition:leave-end="opacity-0 scale-95"
                        class="fixed colorPick"
                    >
                        <div class="bg-white shadow-xs">
                            <div class="flex flex-wrap">
                                <template x-for="(color, index) in colors" :key="index">
                                    <div>
                                        <template x-if="colorSelected === color">
                                            <div
                                                class="inline-flex rounded colorPickButton-selected"
                                                :style="`background: ${color}; outline: 3px solid blue;`"
                                            />
                                        </template>

                                        <template x-if="colorSelected != color">
                                            <div
                                                @click="colorSelected = color; triggerInputEvent()"
                                                @keydown.enter="colorSelected = color; triggerInputEvent()"
                                                role="checkbox" tabindex="0"
                                                :aria-checked="colorSelected"
                                                class="inline-flex rounded colorPickButton"
                                                :style="`background: ${color};`"
                                            />
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>