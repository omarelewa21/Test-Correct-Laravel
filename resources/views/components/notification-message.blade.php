@props([
    'message',
    'title' => null,
    'stretched' => true,
    'type' => 'error',
])

<div {{ $attributes->except('class') }} @class([
        $attributes->get('class'),
        'notification',
        'stretched'     => $stretched,
        'error'         => $type === 'error',
        'warning'       => $type === 'warning',
        'info'          => $type === 'info',
        'informational' => $type === 'informational',
])>
    @isset($title)
        <div class="title">{{ $title }}</div>
    @endisset
    <div class="body">{{ $message }}</div>
</div>