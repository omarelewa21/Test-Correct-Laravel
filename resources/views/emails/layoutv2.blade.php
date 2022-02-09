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
        @yield('support')
        @yield('footer')
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