<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
<head>
    <meta content="text/html;charset=UTF-8" http-equiv="content-type" />

    <link href='http://fonts.googleapis.com/css?family=Open+Sans:400,600,300' rel='stylesheet' type='text/css'>
    <link href='http://maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css' rel='stylesheet' type='text/css'>
    <link href='https://fonts.googleapis.com/css?family=Nunito' rel='stylesheet'>

    <style type="text/css">

        root,
        html,
        body {
            min-width: 100%;
            width: 100%;
            min-height: 100%;
            height: 100%;
            margin: 0;
            padding: 0;
            position: relative;
            display: block;
            overflow-y: auto;
            overflow-x: hidden;
            -webkit-overflow-scrolling: touch;
        }

        body {
            padding-left: 20px;
            padding-right: 20px;
            box-sizing: border-box;
            background-color: #ffffff;
        }

        body * {
            margin: 0px;
            padding: 0px;
            font-size: 15px;
            font-family: "Nunito", sans-serif;
            font-weight: 300;
            color: #041f74;
        }

        body b {
            font-weight: bold;
        }

        body h1 {
            font-size: 24px;
            margin-bottom: 30px;
        }

        body p {
            font-size: 15px;
            line-height: 22px;
            margin-bottom: 15px;
        }

        body h1:last-child,
        body p:last-child,
        body .button:last-child {
            margin-bottom: 0px;
        }

        /*table,*/
        /*table thead,*/
        /*table thead tr,*/
        /*table thead tr th,*/
        /*table tfoot,*/
        /*table tfoot tr,*/
        /*table tfoot tr td {*/
        /*    border-spacing: 0px !important;*/
        /*    border-collapse: collapse !important;*/
        /*    -webkit-border-horizontal-spacing: 0px !important;*/
        /*    -webkit-border-vertical-spacing: 0px !important;*/
        /*}*/

        table{
            border-collapse: separate;
        }
        ol {
            counter-reset: li;
        }

        ol > li {
            list-style: none;
            padding-left: 15px;
            padding-bottom: 25px;
            position: relative;

            color: #2c6d8d;
            font-size: 15px;
            line-height: 22px;
        }

        ol > li:last-child {
            padding-bottom: 0px;
        }

        ol > li span {
            color: #113b50;
        }

        ol > li:before {
            content: counter(li);
            counter-increment: li;

            width: 35px;
            height: 35px;
            position: absolute;
            left: -35px;
            display: inline-block;

            border: 2px solid #2c6d8d;
            border-radius: 50%;
            -o-border-radius: 50%;
            -ms-border-radius: 50%;
            -moz-border-radius: 50%;
            -webkit-border-radius: 50%;

            color: #2c6d8d;
            font-size: 21px;
            text-align: center;
            line-height: 35px;
            letter-spacing: 0px;
        }

        .text-center {
            text-align: center;
        }

        .text-regular {
            font-weight: 400;
        }

        .text-bold {
            font-weight: 600;
        }

        .text-myriad {
            font-family: 'Myriad Pro', 'Arial', sans-serif;
            font-weight: 200;
        }

        .padding-top {
            padding-top: 10px !important;
        }

        .padding-top-xl {
            padding-top: 30px !important;
        }

        .padding-right {
            padding-right: 30px !important;
        }

        .padding-bottom {
            padding-bottom: 10px !important;;
        }

        .padding-bottom-xl {
            padding-bottom: 30px !important;;
        }

        .padding-left {
            padding-left: 30px !important;
        }

        .banner {
            background-color: #2c6d8d;
            text-align: center;
        }

        a {
            color: blue;
        }

        .banner * {
            font-size: 15px;
            color: #ffffff;
        }

        .button {
            background-color: #42b947;
            padding: 15px 30px;
            margin-bottom: 30px;
            display: inline-block;
        }

        .button.danger {
            background-color: #aa0000;
        }

        .button,
        .button * {
            color: #ffffff;
            text-decoration: none;
        }

        .button i {
            margin-right: 5px;
        }

        .table.full {
            width: 100%;
        }

        .table {
            border: 1px solid #cccccc;
        }

        .table tr {
            border-bottom: 1px solid #cccccc;
        }

        .table tr:last-child {
            border-bottom: none;
        }

        .table th,
        .table td {
            padding: 10px;
            border-right: 1px solid #cccccc;
        }

        .table th:last-child,
        .table td:last-child {
            border-right: none;
        }

        td {
            line-height: 22px;
        }

        #wrapper {
            background-color: #ffffff;
            max-width: 720px;
            margin-left: auto;
            margin-right: auto;
            margin-top: 50px;
            margin-bottom: 50px;

            /*border-radius: 5px;*/
            /*-o-border-radius: 5px;*/
            /*-ms-border-radius: 5px;*/
            /*-moz-border-radius: 5px;*/
            /*-webkit-border-radius: 5px;*/

            overflow: hidden;
        }

        #wrapper #header {
            background-color: #ffffff;
        }

        #wrapper #header * {
            color: #ffffff;
        }

        #wrapper #header th {
            height: auto;
            padding: 0px;
            margin: 0px;
        }

        #wrapper #header #logo {
            width: 256px;
        }

        #wrapper #footer {
            background-color: #e2eff6;
        }

        #wrapper #footer td {
            height: auto;
            padding: 0px;
            margin: 0px;

            font-size: 15px;
        }

        #wrapper #footer td h1 {
            margin: 0px;

            font-size: 24px;
        }

        #wrapper #footer #helper {
            background-size: cover;
            background-position: center center;
            width: 75px;
            height: 75px;

            /*border-radius: 50%;*/
            /*-o-border-radius: 50%;*/
            /*-ms-border-radius: 50%;*/
            /*-moz-border-radius: 50%;*/
            /*-webkit-border-radius: 50%;*/
        }

        #footer {
            color: #555555;
            font-size: 14px;
        }

        #footer a {
            color: #333333;
            font-size: 14px !important;
        }
        td{
            padding-left: 40px;
            padding-right: 40px;
        }
        tr.sub-footer>td>p{
            font-size: 14px;
            line-height: 24px;
            font-family: "Nunito", sans-serif;
            color: #6b7789;
        }
        .header_blue{
            background-color: #004df5;
            color: #ffffff;
            padding-top: 20px;
            padding-bottom: 20px;
            font-family: "Nunito", sans-serif;
            font-weight: bold;

        }
        tr.header_blue td{
            border-top-left-radius: 10px !important;
            border-top-right-radius: 10px !important;
            -moz-border-radius-topleft: 10px !important;
            -moz-border-radius-topright: 10px !important;
            -webkit-border-top-left-radius: 10px !important;
            -webkit-border-top-right-radius: 10px !important;
        }
        .header_blue table tr{
            border-bottom: 3px #ffffff;
        }
        .header_intro,.header_message{
            padding-top: 10px;
        }
        .header_intro{
            font-size: 18px;
            color: #ffffff;
        }
        .header_message{
            font-size: 28px;
            color: #ffffff;
            width: 100%;
            border-bottom: 3px #ffffff;
        }
        .header_line{
            height: 3px;
            width: 100%;
            color: #ffffff;
            background-color: #ffffff;
            border-radius: 10px;
            margin-top: 20px;
        }
        .content-td{
            border-left: solid 1px #c3d0ed;
            border-right: solid 1px #c3d0ed;
        }
        .support-section{
            background-color: #f9faff;
        }
        .support-section td{
            border: solid 1px #c3d0ed;
        }
        a.feedback-link{
            color: #041f74;
        }
        .footer{
            background-color: #041f74;
        }
        tr.footer td{
            border-bottom-left-radius: 10px !important;
            border-bottom-right-radius: 10px !important;
            -moz-border-radius-bottomleft: 10px !important;
            -moz-border-radius-bottomright: 10px !important;
            -webkit-border-bottom-left-radius: 10px !important;
            -webkit-border-bottom-right-radius: 10px !important;
            padding-bottom: 20px;
            padding-top: 20px;
        }
        .float-left{
            float: left;
        }
        .float-right{
            float: right;
        }
        .header_table{
            width: 100%;
            margin-top: 20px;
            margin-bottom: 30px;
        }
        .header_table td{
            padding-left: 0px;
            padding-right: 0px;
        }
        .social-svg{
            margin-top: 5px;
            margin-right: 13px;
        }
        .sub-footer td{
            padding-left: 100px;
            padding-top: 30px;
        }
        @yield('applied_styles')
    </style>
</head>

<body leftmargin="0" topmargin="0" marginwidth="0" marginheight="0" style="word-wrap: break-word; -webkit-nbsp-mode: space; -webkit-line-break: after-white-space;">

<table id="wrapper" border="0" width="720px" cellpadding="0" cellspacing="0" style="background:white; width: 720px; margin-left: auto; margin-right: auto; margin: 0px auto;">
    <tbody id="content" style="font-family: Arial; font-size: 15px;">
        <tr>
            <td colspan="999" class="text-center text-regular padding-top padding-right padding-bottom ">
                <svg width="229" height="50" viewBox="0 0 229 50" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <linearGradient x1="49.294%" y1="99.294%" x2="49.294%" y2=".706%" id="a">
                            <stop stop-color="#27973D" offset="0%"/>
                            <stop stop-color="#3DBB56" offset="100%"/>
                        </linearGradient>
                    </defs>
                    <g fill="none" fill-rule="evenodd">
                        <path fill="#004DF5" fill-rule="nonzero" d="M65.38 19.86h-4.555v-3.204h12.964v3.203h-4.604v13.668H65.38zM85.854 26.392h-6.206v4.005h6.932v3.13H75.844V16.656H86.23v3.13h-6.583v3.502h6.206zM89.31 29.596a9.643 9.643 0 0 0 4.23 1.051c1.753 0 2.678-.726 2.678-1.827 0-1.051-.802-1.651-2.828-2.352-2.803-1.002-4.656-2.554-4.656-5.007 0-2.878 2.428-5.055 6.383-5.055a9.884 9.884 0 0 1 4.305.85l-.852 3.054a7.9 7.9 0 0 0-3.503-.8c-1.651 0-2.453.775-2.453 1.626 0 1.078.926 1.552 3.128 2.378 2.978 1.1 4.356 2.653 4.356 5.03 0 2.83-2.153 5.232-6.784 5.232a10.982 10.982 0 0 1-4.78-1.052l.776-3.128zM105.98 19.86h-4.556v-3.204h12.965v3.203h-4.604v13.668h-3.804zM120.574 25.692v2.528h-6.559v-2.528zM138.143 33.076a11.216 11.216 0 0 1-4.305.725c-5.831 0-8.834-3.654-8.834-8.46 0-5.757 4.105-8.935 9.21-8.935a9.569 9.569 0 0 1 4.155.75l-.8 3.005a8.043 8.043 0 0 0-3.204-.626c-3.004 0-5.356 1.827-5.356 5.581 0 3.378 2 5.506 5.38 5.506a9.083 9.083 0 0 0 3.203-.55l.551 3.004zM155.366 24.917c0 5.555-3.33 8.884-8.286 8.884-4.98 0-7.934-3.78-7.934-8.61 0-5.056 3.254-8.835 8.21-8.835 5.181 0 8.01 3.878 8.01 8.56zm-12.19.2c0 3.328 1.551 5.656 4.105 5.656 2.578 0 4.054-2.453 4.054-5.733 0-3.052-1.427-5.655-4.054-5.655-2.603 0-4.105 2.452-4.105 5.731zM157.87 16.881a33.321 33.321 0 0 1 5.08-.35c2.503 0 4.255.376 5.456 1.326a4.28 4.28 0 0 1 1.578 3.554 4.47 4.47 0 0 1-2.98 4.13v.076c1.177.474 1.829 1.576 2.253 3.128.525 1.927 1.026 4.13 1.352 4.782h-3.904a22.542 22.542 0 0 1-1.151-3.93c-.476-2.13-1.202-2.68-2.78-2.704h-1.125v6.634h-3.78V16.88zm3.779 7.259h1.5c1.904 0 3.03-.951 3.03-2.428 0-1.527-1.05-2.327-2.805-2.327a7.614 7.614 0 0 0-1.725.124v4.631zM172.713 16.881a33.314 33.314 0 0 1 5.08-.35c2.503 0 4.255.376 5.456 1.326a4.28 4.28 0 0 1 1.579 3.554 4.469 4.469 0 0 1-2.98 4.13v.076c1.175.474 1.827 1.576 2.253 3.128.525 1.927 1.026 4.13 1.35 4.782h-3.904a22.705 22.705 0 0 1-1.15-3.93c-.475-2.13-1.201-2.68-2.779-2.704h-1.125v6.634h-3.78V16.88zm3.78 7.259h1.5c1.902 0 3.029-.951 3.029-2.428 0-1.527-1.05-2.327-2.804-2.327a7.613 7.613 0 0 0-1.725.124v4.631zM197.591 26.392h-6.205v4.005h6.932v3.13h-10.736V16.656h10.387v3.13h-6.583v3.502h6.205zM213.286 33.076a11.205 11.205 0 0 1-4.305.725c-5.83 0-8.835-3.654-8.835-8.46 0-5.757 4.106-8.935 9.21-8.935a9.57 9.57 0 0 1 4.156.75l-.8 3.005a8.043 8.043 0 0 0-3.204-.626c-3.004 0-5.356 1.827-5.356 5.581 0 3.378 2 5.506 5.38 5.506a9.086 9.086 0 0 0 3.204-.55l.55 3.004zM219.445 19.86h-4.556v-3.204h12.964v3.203h-4.603v13.668h-3.805z"/>
                        <path d="M49.917 24.898A24.958 24.958 0 1 1 24.971 0c13.747.026 24.893 11.15 24.946 24.898M14.459 37.136c.749.08 1.294.115 1.827.195 3.235.501 5.944 2.084 8.453 4.092a15.232 15.232 0 0 0 6.838 3.28c2.427.441 4.645.237 6.733-1.398 6.667-5.211 9.851-11.955 9.184-20.396C46.5 10.293 34.86.883 22.322 2.45A22.665 22.665 0 0 0 3.057 30.688a21.745 21.745 0 0 0 4.222 8.37c.785-.39 1.591-.735 2.416-1.03.771-.23.93-.609.899-1.392-.09-2.243-.153-4.498-.03-6.74.154-2.79 1.22-5.355 2.354-7.88.694-1.543.179-2.362-1.477-2.437-.61-.026-1.222-.094-1.832-.144-.256-.098-.265-.198-.13-.394.643-.562 1.21-1.267 1.949-1.645a18.662 18.662 0 0 1 3.701-1.417 33.307 33.307 0 0 0 6.015-2.158c5.338-2.573 10.748-2 15.986.362a8.478 8.478 0 0 1 3.096 2.708c.654.894 1.15 1.868 2.208 2.32.384.17.637.594.459.9-.178.307-.621.273-.925.262-.65-.027-1.297-.15-1.945-.23.456.427.949.814 1.471 1.156.31.254.864.601.754.974-.112.371-.91.3-1.261.172-2.858-1.023-5.68-.637-8.546-.008-2.24.492-3.653 1.612-4.31 3.899-.451 1.56-1.914 2.369-3.413 2.8-.673.2-1.466-.027-2.202-.064-.213-.051-.247-.117-.093-.316 1.255-.895 2.12-1.964 1.669-3.706-4.803 2.741-7.594 7.027-9.633 12.087" fill="url(#a)"/>
                    </g>
                </svg>
            </td>
        </tr>
        <tr class="header_blue">
            <td colspan="999" class="text-left text-regular padding-top padding-right padding-bottom padding-left">
                <table class="header_table">
                    <tr>
                        <td class="header_intro"><svg width="14" height="16" viewBox="0 0 14 16" xmlns="http://www.w3.org/2000/svg">
                                <g stroke="#FFF" stroke-width="3" fill="none" fill-rule="evenodd" stroke-linecap="round">
                                    <path d="M1.5 7.5h10M6.5 2.5l5 5-5 5"/>
                                </g>
                            </svg>
                            @yield('header_intro')
                        </td>
                    </tr>
                    <tr>
                        <td class="header_message">@yield('header_message')</td>
                    </tr>
                    <tr>
                        <td><hr class="header_line"/></td>
                    </tr>
                </table>
            </td>
        </tr>
        @yield('content')
        <tr class="support-section">
            <td colspan="999" class="text-left text-regular padding-top-xl padding-bottom-xl">
                <p class="text-bold">Heb je vragen of direct hulp nodig?</p>
                <p>Gebruik de chatbutton in je account en je hebt direct verbinding!<br/>
                    Bellen mag ook 010 7171 171</p>
                <p class="text-bold">Wat mis je of kan er beter?</p>
                <p>Test-Correct hoort graag wat je als docent mist of wat er beter kan! Laat het ons via deze weg weten: <a class="feedback-link" href="https://test-correct.nl">vul het feedback formulier in</a></p>
            </td>
        </tr>
        <tr class="footer">
            <td>
                <a href="https://www.facebook.com/TestCorrect">
                    <svg class="social-svg" width="11" height="24" viewBox="0 0 11 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M7.435 24H2.478V11.999H0V7.863h2.478V5.38C2.478 2.006 3.876 0 7.85 0h3.308v4.136H9.09c-1.547 0-1.649.578-1.649 1.657l-.006 2.07h3.746L10.743 12H7.435V24z" fill="#FFF" fill-rule="nonzero"/>
                    </svg>
                </a>
                <a href="https://www.linkedin.com/company/the-teach-%26-learn-company/">
                    <svg class="social-svg" width="24" height="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M5.448 8.4H.303v15.353H5.45V8.401zm.34-4.747C5.753 2.146 4.67 1 2.91 1 1.15 1 0 2.146 0 3.653c0 1.473 1.116 2.652 2.843 2.652h.033c1.795 0 2.911-1.18 2.911-2.652zm7.652 20.1v-8.572c0-.459.034-.918.17-1.245.371-.918 1.218-1.867 2.64-1.867 1.86 0 2.606 1.408 2.606 3.472v8.212H24v-8.802c0-4.716-2.538-6.91-5.923-6.91-2.775 0-3.993 1.538-4.67 2.586h.034V8.401H8.296c.067 1.44 0 15.353 0 15.353h5.144z" fill="#FFF" fill-rule="nonzero"/>
                    </svg>
                </a>
                <a href="https://twitter.com/testcorrect">
                    <svg class="social-svg" width="26" height="24" viewBox="0 0 26 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M8.19 23.2c9.826 0 15.2-8.08 15.2-15.087 0-.23 0-.458-.015-.685a10.825 10.825 0 0 0 2.665-2.745c-.975.429-2.01.71-3.069.834a5.328 5.328 0 0 0 2.35-2.932 10.756 10.756 0 0 1-3.393 1.287 5.377 5.377 0 0 0-6.419-1.044 5.293 5.293 0 0 0-2.686 5.88 15.207 15.207 0 0 1-11.01-5.54 5.282 5.282 0 0 0 1.653 7.078 5.333 5.333 0 0 1-2.424-.664v.067c0 2.524 1.793 4.698 4.286 5.198a5.373 5.373 0 0 1-2.413.091 5.344 5.344 0 0 0 4.992 3.682A10.772 10.772 0 0 1 0 20.818a15.209 15.209 0 0 0 8.19 2.378" fill="#FFF" fill-rule="nonzero"/>
                    </svg>
                </a>
                <a href="https://test-correct.nl">
                    <svg class="social-svg" width="24" height="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <g transform="translate(1.5 1)" stroke="#FFF" fill="none" fill-rule="evenodd">
                            <circle stroke-width="3" cx="10.633" cy="11" r="10.633"/>
                            <ellipse stroke-width="2" cx="10.633" cy="11" rx="4.4" ry="11"/>
                            <path stroke-width="2" stroke-linecap="square" d="M1.467 7.333H19.8M1.467 15.4H19.8"/>
                        </g>
                    </svg>
                </a>
                <svg class="float-right" width="183" height="40" viewBox="0 0 183 40" xmlns="http://www.w3.org/2000/svg">
                    <g fill="#FFF" fill-rule="evenodd">
                        <g fill-rule="nonzero">
                            <path d="M52.304 15.887H48.66v-2.562h10.372v2.562h-3.684v10.934h-3.044zM68.683 21.113h-4.965v3.204h5.546v2.504h-8.59V13.325h8.31v2.503h-5.266v2.803h4.965zM71.448 23.677a7.714 7.714 0 0 0 3.384.841c1.402 0 2.143-.58 2.143-1.462 0-.84-.642-1.32-2.263-1.881-2.243-.802-3.725-2.043-3.725-4.006 0-2.303 1.942-4.044 5.107-4.044a7.908 7.908 0 0 1 3.444.68l-.682 2.443a6.32 6.32 0 0 0-2.803-.64c-1.32 0-1.962.62-1.962 1.301 0 .862.74 1.242 2.502 1.902 2.383.88 3.486 2.122 3.486 4.025 0 2.262-1.723 4.185-5.428 4.185a8.786 8.786 0 0 1-3.823-.842l.62-2.502zM84.785 15.887h-3.646v-2.562h10.373v2.562h-3.684v10.934h-3.043zM96.46 20.554v2.022h-5.248v-2.022zM110.515 26.46a8.973 8.973 0 0 1-3.444.58c-4.665 0-7.068-2.923-7.068-6.768 0-4.604 3.284-7.147 7.368-7.147a7.655 7.655 0 0 1 3.324.6l-.64 2.403a6.435 6.435 0 0 0-2.563-.5c-2.403 0-4.285 1.461-4.285 4.465 0 2.703 1.6 4.405 4.304 4.405a7.266 7.266 0 0 0 2.563-.44l.44 2.402zM124.292 19.933c0 4.444-2.663 7.108-6.628 7.108-3.984 0-6.347-3.024-6.347-6.888 0-4.045 2.603-7.068 6.568-7.068 4.145 0 6.407 3.103 6.407 6.848zm-9.752.16c0 2.663 1.242 4.525 3.285 4.525 2.062 0 3.243-1.962 3.243-4.586 0-2.442-1.141-4.524-3.243-4.524-2.083 0-3.285 1.961-3.285 4.585zM126.296 13.505a26.657 26.657 0 0 1 4.064-.28c2.003 0 3.404.3 4.365 1.06a3.423 3.423 0 0 1 1.262 2.844 3.575 3.575 0 0 1-2.383 3.304v.06c.94.38 1.462 1.261 1.802 2.503.42 1.541.82 3.305 1.08 3.825h-3.122a18.033 18.033 0 0 1-.921-3.144c-.38-1.703-.961-2.143-2.224-2.163h-.9v5.307h-3.023V13.505zm3.023 5.807h1.2c1.523 0 2.425-.76 2.425-1.943 0-1.221-.841-1.861-2.244-1.861a6.091 6.091 0 0 0-1.381.1v3.704zM138.17 13.505a26.65 26.65 0 0 1 4.065-.28c2.002 0 3.403.3 4.364 1.06a3.424 3.424 0 0 1 1.263 2.844 3.575 3.575 0 0 1-2.383 3.304v.06c.94.38 1.461 1.261 1.802 2.503.42 1.541.82 3.305 1.08 3.825h-3.123a18.164 18.164 0 0 1-.92-3.144c-.38-1.703-.961-2.143-2.224-2.163h-.9v5.307h-3.024V13.505zm3.024 5.807h1.2c1.522 0 2.424-.76 2.424-1.943 0-1.221-.84-1.861-2.243-1.861a6.09 6.09 0 0 0-1.381.1v3.704zM158.073 21.113h-4.965v3.204h5.546v2.504h-8.589V13.325h8.31v2.503h-5.267v2.803h4.965zM170.629 26.46a8.964 8.964 0 0 1-3.444.58c-4.664 0-7.068-2.923-7.068-6.768 0-4.604 3.284-7.147 7.368-7.147a7.656 7.656 0 0 1 3.325.6l-.64 2.403a6.434 6.434 0 0 0-2.563-.5c-2.404 0-4.285 1.461-4.285 4.465 0 2.703 1.6 4.405 4.304 4.405a7.269 7.269 0 0 0 2.563-.44l.44 2.402zM175.556 15.887h-3.645v-2.562h10.371v2.562H178.6v10.934h-3.044z"/>
                        </g>
                        <path d="M39.934 19.918A19.967 19.967 0 1 1 19.976 0c10.999.02 19.915 8.92 19.958 19.918M11.567 29.71c.6.064 1.035.092 1.462.156 2.588.4 4.755 1.667 6.762 3.273a12.185 12.185 0 0 0 5.47 2.624c1.942.353 3.717.19 5.387-1.118 5.334-4.169 7.881-9.564 7.347-16.317C37.2 8.234 27.888.707 17.858 1.96A18.132 18.132 0 0 0 2.446 24.55a17.396 17.396 0 0 0 3.378 6.695 17.877 17.877 0 0 1 1.932-.824c.617-.184.744-.487.72-1.113-.072-1.794-.123-3.598-.024-5.391.122-2.233.976-4.285 1.882-6.306.555-1.234.143-1.889-1.18-1.948-.49-.022-.979-.076-1.467-.116-.205-.078-.212-.158-.103-.315.514-.45.967-1.014 1.558-1.316a14.93 14.93 0 0 1 2.961-1.133 26.646 26.646 0 0 0 4.812-1.727c4.27-2.059 8.598-1.6 12.789.29a6.783 6.783 0 0 1 2.477 2.166c.523.715.92 1.495 1.766 1.856.307.136.51.475.367.72-.142.246-.497.218-.74.21-.52-.022-1.037-.12-1.556-.184.365.341.76.65 1.178.925.247.203.69.48.602.779-.089.296-.727.239-1.008.137-2.287-.818-4.545-.51-6.837-.006-1.792.393-2.923 1.29-3.448 3.119-.361 1.249-1.531 1.895-2.73 2.24-.539.16-1.173-.021-1.762-.051-.17-.041-.198-.094-.075-.253 1.005-.716 1.696-1.571 1.336-2.965-3.843 2.193-6.075 5.622-7.707 9.67"/>
                    </g>
                </svg>
            </td>
        </tr>
        <tr class="sub-footer">
            <td>
                <p>www.test-correct.nl, Dotterbloemstraat 25, 3053 JV, Rotterdam, Nederland</p>
                <p>Je kunt je niet afmelden voor belangrijke e-mails over je account, zoals deze.</p>
            </td>
        </tr>
    </tbody>

</table>
</body>
</html>