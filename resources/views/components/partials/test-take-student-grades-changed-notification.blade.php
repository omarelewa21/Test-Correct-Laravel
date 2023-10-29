@props(['show' => true])
@if($show)
    <div class="notification warning stretched">
        <div class="flex items-center gap-2">
            <x-icon.exclamation/>
            <div class="title">@lang('test-take.grading_changed_title')</div>
        </div>
        <div class="body">
            <span>@lang('test-take.grading_changed_body')</span>
        </div>
    </div>
@endif