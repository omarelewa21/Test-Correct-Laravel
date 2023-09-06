<a href="{{ $appStoreLink }}"
   selid="{{ $selid }}"
   @class(["button cta-button space-x-2.5 focus:outline-none", $attributes->get('class'), $size ])
>
    <span>@lang('student.Verder in Test-Correct app')</span>
    <x-dynamic-component :component="'icon.'.$iconName"/>
</a>