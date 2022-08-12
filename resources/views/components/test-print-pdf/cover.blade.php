<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="file://{{ public_path('/css/app_pdf.css') }}">
    <link rel="stylesheet" href="file://{{ public_path('/css/print-test-pdf.css') }}">
</head>

<body class="test-print-pdf" style="margin: 0; border: 0; ">
<div class="cover-container-1" >
    <div>{{ $test->name }}</div>
</div>

<div class="cover-container-2">
    @if($attachmentsText)
    <div>{{ $attachmentsText }}</div>
    @endif
</div>

<div class="cover-container-3">
    <div>
        {!! __('test-pdf.cover description text 1') !!}
    </div>
    <div>
        {!! __('test-pdf.cover description text 2') !!}
    </div>
</div>

</body>
</html>