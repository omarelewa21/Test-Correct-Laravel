<div
    x-data="{}"
    x-init="initializeIntenseWrapper('{!! config('app.intense.apiKey') !!}', {!!config('app.intense.debugMode') ? 'true' : 'false'!!}, {{ $deviceId}}, {{ $sessionId}}, '{{ md5('1.1') }}')"
>
</div>
