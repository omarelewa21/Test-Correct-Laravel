@props(['color' => 'grey'])
<span {{ $attributes->except('class') }} @class(['knightrider-container',$attributes->get('class'), $color])>
    <span @class(["knightrider-bar animate-knightrider"])></span>
</span>