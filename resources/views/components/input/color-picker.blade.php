@props(['id', 'name', 'title'])

<div x-data="{
    isOpen: false,
    colorSelected: '#000000',
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
    ]}"
    x-cloak
>
    <div class="max-w-sm mx-auto py-16">
        <div class="mb-5">
            <div class="flex items-center">
                <div class="relative ml-3 mt-8">
                <!-- Selector Input -->
                    <input
                    id="{{$id}}"
                    name="{{$name}}"
                    title="{{$title}}"
                    autocomplete="off"
                    type="color"
                    class="cursor-pointer"
                    :title=""
                    @click.prevent="isOpen = !isOpen"
                    :value="`${colorSelected}`"
                    >
                    
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
                    class="fixed mt-2 colorPick"
                    >
                        <div class="bg-white shadow-xs px-4 py-3">
                            <div class="flex flex-wrap mx-auto overflow-auto" style="height: 200px;">
                                <template x-for="(color, index) in colors" :key="index">
                                    <div class="px-2">
                                        <template x-if="colorSelected === color">
                                            <div
                                            class="w-7 h-7 inline-flex rounded border-4 colorPickButton"
                                            :style="`background: ${color}; box-shadow: 0 0 0 2px rgba(0, 0, 0, 0.2);`"
                                            />
                                        </template>

                                        <template x-if="colorSelected != color">
                                            <div
                                            @click="colorSelected = color"
                                            @keydown.enter="colorSelected = color"
                                            role="checkbox" tabindex="0"
                                            :aria-checked="colorSelected"
                                            class="w-7 h-7 inline-flex rounded border-4 colorPickButton"
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