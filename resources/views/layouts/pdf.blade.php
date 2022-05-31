<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta charset="UTF-8">
    <meta name="_token" content="{{ csrf_token() }}">
    <meta http-equiv="refresh" content="{{ config('session.lifetime') * 60 }}">
    <title version="{{ \tcCore\Http\Helpers\BaseHelper::getCurrentVersion() }}">Test-Correct</title>
    <link rel="icon" href="{{ asset('img/icons/Logo-Test-Correct-recolored-icon-only.svg') }}"/>
    {{--    <link href="https://unpkg.com/tailwindcss@^2/dist/tailwind.min.css" rel="stylesheet">--}}
    <script src="/ckeditor/ckeditor.js" type="text/javascript"></script>
    <script src="{{ mix('/js/ckeditor.js') }}" type="text/javascript"></script>
    @if(!is_null(Auth::user())&&Auth::user()->text2speech)
        <link rel="stylesheet" type="text/css" href="{{ mix('/css/rs_tlc.css') }}" />
    @endif
    @livewireStyles
    <link rel="stylesheet" href="{{ public_path('/css/app.css') }}">
    @if(config('bugsnag.browser_key') != '')
        <script src="//d2wy8f7a9ursnm.cloudfront.net/v7/bugsnag.min.js"></script>
        <script>Bugsnag.start({ apiKey: '{{ config('bugsnag.browser_key') }}' })</script>
    @endif
    @stack('styling')
    <style>
        [x-cloak] {
            visibility: visible !important;
        }
        body{
            background: #ffffff !important;
        }
        .question-indicator .question-number.complete {
            border-color: #3ab753;
            background: #3ab753;
            color: white;
        }
        .question-title .question-number {
            margin-bottom: 0;
            margin-right: 0;
        }
        .question-title {
            padding-bottom: 1rem;
            border-bottom: 3px solid #041f74;
        }

        .question-indicator .question-number {
            position: relative;
            min-width: 30px;
            height: 30px;
            font-size: 14px;
            font-weight: bold;
            border: solid 3px;
        }
        .question-number>span {
            font-size: 18px;
            font-weight: bold;
        }
        h1 {
            color: #041f74;
        }
        .form-input {
            background: #f9faff;
            border: 1px solid #c3d0ed;
        }
        p{
            vertical-align: top;
        }
        .multiple-choice-question.active {
            box-sizing: border-box;
            border-color: #004df5;
            border-width: 2px;
            background: #f9faff;
            font-weight: bold;
            color: #004df5;
        }
        .stroke-current {
            stroke: #004df5;
        }
        .icon_checkmark_pdf{
            display: inline-flex;
            margin-left: 10px;
            /*width: 16px;*/
            /*height: 16px;*/
            max-width: none !important;
        }
        .multiple-choice-question.disabled, .multiple-choice-question.disabled:hover {
            background: white;
            border: solid 2px #c3d0ed;
            color: #929DAF;
            box-shadow: none;
            font-weight: normal;
        }
        .overview .trueFalse.active, .overview .trueFalse.active:hover {
            border: 2px solid #004df5;
            color: #004df5;
        }
        .bg-blue-grey {
            background: #c3d0ed;
        }
        .border-blue-grey {
            border-color: #c3d0ed;
        }
        .bg-off-white {
            background: #f9faff;
        }
        .overview .trueFalse, .overview .trueFalse:hover {
            border: 1px solid transparent;
            margin: 0;
            color: #929DAF;
        }
        .divider {
            height: 3px;
            background: #041f74;
        }
        .mc-radio{
            margin-top: 8px;
        }
        .bg-primary-light {
            background: #e6edfa;
        }
        .border-light-grey {
            border-color: #F0F2F5;
        }
        .bg-light-grey {
            background: #F0F2F5;
        }
        .border-mid-grey {
            border-color: #929DAF;
        }
        .border-system-secondary {
            border-color: #CEDAF3;
        }
        .border-primary {
            border-color: #004df5;
        }
        .base {
            color: #041f74;
        }
        .matching-dropzone{
            height: 44px;
            margin-left: 40px;
        }
        .label-dropzone>span{
            min-height: 27px;
        }
        .label-dropzone{
            padding: 0;
        }
        .space-x-2{
            margin-top: 12px;
        }
        .classified>div{
            display: inline-flex;
        }
        .pdf-35{
            width: 35%;
        }
        .pdf-45{
            width: 45%;
        }
        .pdf-ml-2{
            margin-left: 10px;
        }
        .pdf-ml-5{
            margin-left: 25px;
        }
        .pdf-ml-8{
            margin-left: 40px;
        }
        .pdf-ml-9{
            margin-left: 45px;
        }
        .pdf-mt-2{
            margin-top: 10px;
        }
        .pdf-100{
            width: 100%;
        }
        .pdf-90{
            width: 90%;
        }
        .pdf-80{
            width: 80%;
        }
        .pdf-minh-40{
            min-height: 40px;
        }
        .pdf-align-center{
            vertical-align: sub;
        }
        .pdf-dropzone{
            padding-right: 40px;
        }
        .pdf-answer-model-select{
            display: inline-flex;
            border: solid 2px #c3d0ed;
            padding-left: 10px;
            padding-right: 10px;
        }
        .prevent-pagebreak{
            page-break-before:auto;
        }
        .prevent-pagebreak-table{
            width: 100%;
            border: 0 !important;
            border-width: 0 !important;
            border-collapse: unset;
        }
        .questionContainer .prevent-pagebreak-table td{
            border: 0 !important;
            border-width: 0 !important;
        }
    </style>




</head>
<body id="body" class="flex flex-col min-h-screen" onload="addIdsToQuestionHtml()">
{{ $slot }}

@livewireScripts
<script>
    window.livewire.onError(statusCode => {

        if (statusCode === 406) {
            Livewire.emit('set-force-taken-away');

            return false;
        }
        if (statusCode === 440 || statusCode === 419 || statusCode === 401 || statusCode === 403) {
            location.href = '{{ config('app.url_login') }}';

            return false
        }
    })
</script>
<script src="{{ mix('/js/app.js') }}"></script>
<script src="https://www.wiris.net/demo/plugins/app/WIRISplugins.js?viewer=image"></script>
@if(!is_null(Auth::user())&&Auth::user()->text2speech)
<script src="//cdn-eu.readspeaker.com/script/12749/webReader/webReader.js?pids=wr&amp;noDefaultSkin=1&amp;&mobile=0&amp;language={{Auth::user()->getLanguageReadspeaker()}}" type="text/javascript" id="rs_req_Init"></script>
<script src="{{ mix('/js/readspeaker_tlc.js') }}"></script>
<script>
    readspeakerLoadCore();
</script>
@endif
@stack('scripts')
<script>
    Alpine.start();
    Core.init();
</script>
</body>
</html>
