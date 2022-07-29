<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="file://{{ public_path('/css/app_pdf.css') }}">
    <link rel="stylesheet" href="file://{{ public_path('/css/print-test-pdf.css') }}">
</head>
<body class="test-print-pdf" style="border:0; margin: 0;" onload="subst()">

<div class="footer-line"></div>

<div class="footer-info-container">
    <div class="">
        <div class="info-title">{{ __('test-pdf.teacher') }}</div>
        <div class="info-value">{{ $test->author->name }}</div>
    </div>
    <div class="">
        <div class="info-title">{{ __('test-pdf.max points') }}</div>
        <div class="info-value">{{ $data['maxScore'] }}</div>
    </div>
    <div class="">
        <div class="info-title">{{ __('test-pdf.weighting') }}</div>
        <div class="info-value">{{ $data['weight'] ?? '' }}</div>
    </div>
    <div class="">
        <div class="info-title">{{ __('test-pdf.number of questions') }}</div>
        <div class="info-value">{{ $data['amountOfQuestions'] }}</div>
    </div>
</div>

<div class="footer-line"></div>
<table style="border:0; width: 100%;height: 20px; bottom: 0; ">
    <tr>
        <td colspan="3" style="width: 10em">
        </td>
    </tr>
    <tr>
        <td style="text-align: left; width: 33%; ">

        </td>
        <td style="text-align: center; width: 33%">
            <span id="Title"> {{ $test->name }} </span>
        </td>
        <td style="text-align: right; width: 33%">

        </td>
    </tr>
</table>
</body>
</html>