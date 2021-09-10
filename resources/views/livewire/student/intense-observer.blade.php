<div
    x-data="{deviceId: {{ $deviceId }}, sessionId: {{ $sessionId }}, code: '{{ md5('1.1') }}'}"
    x-init="initializeIntenseWrapper('{!! config('app.intense.apiKey') !!}', {!!config('app.intense.debugMode') ? 'true' : 'false'!!})"
>
</div>
