@if($rating != null)
    <span class="inline-flex items-center justify-center min-w-[32px] min-h-[32px] text-sm rounded-full {{ $bgColor }}">
        <span class="inline-flex px-2">{!! str_replace('.',',', round($rating, 1))!!}</span>
    </span>
@else
    <span title="{{ __('student.no_grade') }}" class="border border-mid-grey inline-flex items-center justify-center min-w-[32px] min-h-[32px] text-sm rounded-full bg-white">
        <span class="inline-flex px-2 note font-bold">?</span>
    </span>
@endif