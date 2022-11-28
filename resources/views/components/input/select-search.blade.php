<div class="select-search" :class="{'mt-1' : '{{$level}}'!='top'}" wire:key="select_{{ $name }}">
    <div
            x-data="selectSearch({value:@entangle($attributes->wire('model')), data: @entangle($name), emptyOptionsMessage: '{{ $emptyOptionsMessage }}', name: '{{ $name }}', placeholder: '{{ $placeholder }}' })"
            @click.away="closeListbox()"
            @keydown.escape="closeListbox()"
            :class="{'sub-item-with-connecting-line ml-3' : '{{$level}}'==='sub','sub-item-with-connecting-line ml-6' : '{{$level}}'==='subsub'}"

            class="relative h-10"
            x-effect="()=> options = data; "
    >
                <span class="inline-block w-full h-full rounded-md shadow-sm">
                      <button
                              x-ref="button"
                              @click="toggleListboxVisibility()"
                              :aria-expanded="open"
                              aria-haspopup="listbox"
                              class="relative z-0 w-full h-full py-2 pl-3 pr-10 text-left transition duration-150 ease-in-out border rounded-10 border-bluegrey  cursor-default focus:outline-none focus:shadow-outline-blue focus:border-blue-300 text-base sm:leading-5"
                              :class="@js($disabled) ? 'bg-white' : 'bg-offwhite'"
                              :title="value in options ? options[value] : placeholder"
                              @if($disabled) disabled @endif
                      >
                            <span
                                    x-show="! open"
                                    x-text="value in options ? options[value] : placeholder"
                                    :class="{ 'text-midgrey': ! (value in options) }"
                                    class="block truncate"
                            ></span>

                            <input
                                    x-ref="search"
                                    x-show="open"
                                    x-model="search"
                                    @keydown.enter.stop.prevent="selectOption()"
                                    @keydown.arrow-up.prevent="focusPreviousOption()"
                                    @keydown.arrow-down.prevent="focusNextOption()"
                                    type="search"
                                    class="w-full h-full form-control outline-none focus:outline-none focus-within:outline-none focus-visible:outline-none bg-offwhite"
                            />

                            <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400" viewBox="0 0 20 20" fill="none" stroke="currentColor">
                                    <path d="M7 7l3-3 3 3m0 6l-3 3-3-3" stroke-width="1.5" stroke-linecap="round"
                                          stroke-linejoin="round"></path>
                                </svg>
                            </span>
                      </button>
                </span>

        <div
                x-show="open"
                x-transition:leave="transition ease-in duration-100"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                x-cloak
                class="absolute z-30 w-full bg-white main-shadow rounded-10 mt-1"
        >
            <div
                    style=""
                    x-ref="listbox"
                    @keydown.enter.stop.prevent="selectOption()"
                    @keydown.arrow-up.prevent="focusPreviousOption()"
                    @keydown.arrow-down.prevent="focusNextOption()"
                    role="listbox"
                    :aria-activedescendant="focusedOptionIndex ? name + 'Option' + focusedOptionIndex : null"
                    tabindex="-1"
                    class="py-2 px-0 overflow-auto text-base max-h-60 focus:outline-none list-none"
            >
                <template x-for="(key, index) in Object.keys(options)" :key="index">
                    <div
                            :id="name + 'Option' + focusedOptionIndex"
                            @click="selectOption()"
                            @mouseenter="focusedOptionIndex = index"
                            @mouseleave="focusedOptionIndex = null"
                            role="option"
                            :aria-selected="focusedOptionIndex === index"
                            :class="{ 'text-white bg-primary': index === focusedOptionIndex, 'text-gray-900': index !== focusedOptionIndex }"
                            class="relative py-2 cursor-default select-none px-2"
                            :title="Object.values(options)[index]"
                    >
                                <span x-text="Object.values(options)[index]"
                                      :class="{ 'font-semibold': index === focusedOptionIndex, 'font-normal': index !== focusedOptionIndex }"
                                      class="block font-normal truncate"
                                ></span>

                        <span
                                x-show="key === value"
                                :class="{ 'text-white': index === focusedOptionIndex, 'text-sysbase': index !== focusedOptionIndex }"
                                class="absolute inset-y-0 right-0 flex items-center pr-4 text-indigo-600"
                        >
                                    <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd"
                                              d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                              clip-rule="evenodd"/>
                                    </svg>
                                </span>
                    </div>
                </template>

                <div
                        x-show="! Object.keys(options).length"
                        x-text="emptyOptionsMessage"
                        class="px-3 py-2 text-gray-900 cursor-default select-none"></div>
            </div>
        </div>

    </div>

</div>
