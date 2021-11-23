@if(!session()->get('isInBrowser'))
    <span class="text-xs absolute min-w-max bottom-0 left-[60px] {{ $status }}">
        {{ __('student.version') }}: {{ $version  }}
    </span>
@endif