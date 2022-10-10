@if($rating != null)
    <span class="px-2 py-1 text-sm rounded-full {{ $bgColor }}">{!! str_replace('.',',',round($rating, 1))!!}</span>
@else
    <span>{{ __('student.no_grade') }}</span>
@endif