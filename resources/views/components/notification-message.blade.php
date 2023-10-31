@props([
    'title',
    'message' => null,
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
        'with-message'  => isset($message),
        'without-message' => !isset($message),
])>
    <div class="title">{{ $title }}</div>

    @isset($message)
        <div class="body">{{ $message }}</div>
    @endisset
</div>