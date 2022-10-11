@if($rating != null)
    <span class="inline-flex items-center justify-center min-w-[30px] min-h-[30px] text-sm rounded-full {{ $bgColor }}">
        <span class="inline-flex px-2">{!! str_replace('.',',',round($rating, 1))!!}</span>
    </span>
@else
    <span>{{ __('student.no_grade') }}</span>
@endif