{{--
-- Important note:
--
-- This template is based on an example from Tailwind UI, and is used here with permission from Tailwind Labs
-- for educational purposes only. Please do not use this template in your own projects without purchasing a
-- Tailwind UI license, or they’ll have to tighten up the licensing and you’ll ruin the fun for everyone.
--
-- Purchase here: https://tailwindui.com/
--}}

@props([
'sortable' => null,
'direction' => null,
'multiColumn' => null,
'width' => 'auto',
'textAlign' => 'left',
'column' => null,
])

<th {{ $attributes->merge(['class' => 'text-'.$textAlign.' px-3 w-auto']) }}
    style="width: {{ $width }}"
>
    @unless ($sortable)
        <span class="text-{{$textAlign}} body2 bold">{{ $slot }}</span>
    @else
        <x-button.text class="flex space-x-1 {{ $textAlign == 'right' ? 'ml-auto' : ''}}  body2 bold group focus:outline-none">
            <span>{{ $slot }}</span>

            <span class="relative flex items-center">
                @if ($multiColumn)
                    @if ($direction === 'asc')
                        <svg class="w-3 h-3 group-hover:opacity-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                             xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round"
                                                                      stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
                        <svg class="w-3 h-3 opacity-0 group-hover:opacity-100 absolute" fill="none"
                             stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path
                                    stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7"></path></svg>
                    @elseif ($direction === 'desc')
                        <div class="opacity-0 group-hover:opacity-100 absolute">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                 xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round"
                                                                          stroke-width="2"
                                                                          d="M5 15l7-7 7 7"></path></svg>
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                 xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round"
                                                                          stroke-width="2"
                                                                          d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                        <svg class="w-3 h-3 group-hover:opacity-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                             xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round"
                                                                      stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    @else
                        <svg class="w-3 h-3 opacity-0 group-hover:opacity-100 transition-opacity duration-300"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path
                                    stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 15l7-7 7 7"></path></svg>
                    @endif
                @else
                    @if ($direction === 'asc')
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                             xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round"
                                                                      stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
                    @elseif ($direction === 'desc')
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                             xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round"
                                                                      stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    @else
                        <svg class="w-3 h-3 opacity-0 group-hover:opacity-100 transition-opacity duration-300"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path
                                    stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 15l7-7 7 7"></path></svg>
                    @endif
                @endif
            </span>
        </x-button.text>
    @endif
</th>
