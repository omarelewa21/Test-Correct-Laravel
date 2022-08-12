<!DOCTYPE html>
<html><head>
    <link rel="stylesheet" href="file://{{ public_path('/css/app_pdf.css') }}">
    <link rel="stylesheet" href="file://{{ public_path('/css/print-test-pdf.css') }}">
</head>
<body class="test-print-pdf" style="border:0; margin: 0;" onload="subst()">
<table class="header-table" style="width: 100%; border: none !important; border-color: white;">
    <tr style="border: none !important; border-color: white;">
        <td class=" bold" rowspan="2" style="border: none !important; border-color: white;">
            <img class="h-12" src="{{ public_path('/img/mail/logo-test-correct.png') }}"
                 alt="Test-Correct">
        </td>
        <td class="" style="text-align:right; border: none !important; border-color: white;">
            {{ __("test-pdf.".$testType) }} {{ $test->educationLevel->name }}
        </td>
    </tr>
    <tr>
        <td class="bold text-right">
            {{ $test->period->schoolYear->year }}
        </td>
    </tr>
</table>
<div class="header-line mt-3"></div>

<div class="header-info-container">
    <div class="bold">{{ $test->subject->name }}</div>
    <div>
        <span class="inline">{{ $period }}</span>
        @if($date)
        <span class="inline pl-3">{{ $date }}</span>
        @endif
    </div>
</div>

<div class="header-line mt-2 mb-3"></div>
</body></html>