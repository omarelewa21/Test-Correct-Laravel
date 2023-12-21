<!DOCTYPE html>
<html><head>
    <link rel="stylesheet" href="file://{{ public_path('/css/app_pdf.css') }}">
    <link rel="stylesheet" href="file://{{ public_path('/css/print-test-pdf.css') }}">
</head>
<body class="ck-content test-print-pdf {{ $extraCssClass }}" style="margin: 0;" onload="subst()">
<table class="header-table" style="width: 100%">
    <tr>
        <td class=" bold" rowspan="2">
            <img class="h-12" src="{{ public_path('/img/mail/logo-test-correct.png') }}"
                 alt="Test-Correct">
        </td>
        <td class="" style="text-align:right">
            {{ __("test-pdf.".$testType) }} {{ $test->educationLevel->name }}
        </td>
    </tr>
    <tr>
        <td class="bold text-right">
            {{ \tcCore\Lib\Repositories\PeriodRepository::getCurrentPeriod()->schoolYear->year }}
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