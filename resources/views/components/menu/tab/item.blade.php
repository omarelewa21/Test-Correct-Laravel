@props([
    'tab',
    'menu',
    'highlight' => false,
    'when' => true,
    'selid' => false,
])

@if($when)
    <div {{ $attributes->except('class') }}
         @class([
              'flex items-center relative hover:text-primary hover:bg-primary/5 active:text-primary active:bg-primary/10 px-2 cursor-pointer transition',
              $attributes->get('class'),
              'group' => $highlight,
            ])
         x-on:click="{{ $menu }} = @js($tab)"
    >
        @if($highlight)
            <span class="bold text-white bg-sysbase px-2 py-1 rounded-lg group-hover:bg-primary transition"
                  x-bind:class="{'bg-primary' : {{ $menu }} === @js($tab) }"
                  {{ $selid ? 'selid=tab_'.$tab : '' }}

            >
            {{ $slot }}
        </span>
        @else
            <span class="bold" x-bind:class="{{ $menu }} === @js($tab) ? 'primary' : '' "
                  {{ $selid ? 'selid=tab_'.$tab : '' }}
            >
            {{ $slot }}
        </span>
        @endif
        <span class="absolute w-[calc(100%-1rem)] bottom-0 left-2" style="height: 3px" x-bind:class="{{ $menu }} === @js($tab) ? 'bg-primary' : 'bg-transparent' "></span>
    </div>
@endif