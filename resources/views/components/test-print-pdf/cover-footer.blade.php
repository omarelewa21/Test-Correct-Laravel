<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="file://{{ public_path('/css/app_pdf.css') }}">
    <link rel="stylesheet" href="file://{{ public_path('/css/print-test-pdf.css') }}">
</head>
<body class="ck-content test-print-pdf {{ $extraCssClass }}" style="border:0; margin: 0;" onload="subst()">

<div class="footer-line"></div>

<div class="footer-info-container">
    @if($data['teacher'])
    <div class="">
        <div class="info-title">{{ __('test-pdf.teacher') }}</div>
        <div class="info-value">{{ $data['teacher'] }}</div>
    </div>
    @endif
    <div class="{{ $data['weight'] ? '' : 'info-extra-margin' }}">
        <div class="info-title">{{ __('test-pdf.max points') }}</div>
        <div class="info-value">{{ $data['maxScore'] }}</div>
    </div>
    @if($data['weight'])
    <div class="">
        <div class="info-title">{{ __('test-pdf.weighting') }}</div>
        <div class="info-value">{{ $data['weight'] ?? '' }}</div>
    </div>
    @endif
    <div class="">
        <div class="info-title">{{ __('test-pdf.number of questions') }}</div>
        <div class="info-value">{{ $data['amountOfQuestions'] }}</div>
    </div>
</div>

<div class="footer-line"></div>
<table class="footer-table" style="width: 100%;height: 20px; bottom: 0; ">
    <tr>
        <td colspan="3" style="width: 10em">
        </td>
    </tr>
    <tr>
        <td colspan="3" style="text-align: center; width: 33%">
            <span id="Title"> {{ $test->name }} </span>
        </td>
    </tr>
</table>
</body>
</html>