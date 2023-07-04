@props([
'size' => 'sm',
'rotateIcon' => false,
'selid' => 'download-app-from-store',
'type',
'disabled' => false
])
<?php

?>
@if(\tcCore\Http\Helpers\AppVersionDetector::osIsWindows())
    <a selid="{{ $selid }}" {{ $attributes->merge(['class' => 'button cta-button space-x-2.5 focus:outline-none '  .$size]) }}
        href="https://www.microsoft.com/en-us/p/test-correct/9p5knbs4r6n0?activetab=pivot:overviewtab"
    >
        Windows
        {{ $slot }}
    </a>
@elseif(\tcCore\Http\Helpers\AppVersionDetector::osIsMac())
    <a selid="{{ $selid }}"
            {{ $attributes->merge(['class' => 'button cta-button space-x-2.5 focus:outline-none '  .$size]) }}
          href="https://apps.apple.com/nl/app/test-correct/id1478736834?l=en"
    >
        {{ $slot }} MAC
    </a>
@endif
