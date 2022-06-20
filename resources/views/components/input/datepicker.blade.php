@props([
    'multiple' => false,
    'locale' => 'nl',
    'minDate' => false,
])

<div wire:ignore
     class="inline-flex rounded-10 relative"
     x-data="flatpickr(@entangle($attributes->wire('model')),
                '{{$multiple ? 'range' : 'single' }}',
                '{{$locale}}',
                '{{$minDate}}'
             )
    ">
    <input type="text" x-ref="datepickr" class="datepicker w-full form-input {{$multiple ? 'range' : 'single'}}">
    <div class="absolute right-2 top-1 h-[32px] w-[24px] flex items-center pointer-events-none">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </svg>
    </div>
</div>