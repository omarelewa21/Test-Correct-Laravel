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
                <img src="{{config('app.base_url')}}img/mail/logo-test-correct.png">
            </td>
        </tr>
        <tr class="header_blue">
            <td colspan="999" class="text-left text-regular padding-top padding-right padding-bottom padding-left">
                <table class="header_table">
                    <tr>
                        <td class="header_intro">
                            <img src="{{config('app.base_url')}}img/mail/icons-arrow-white.png">
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