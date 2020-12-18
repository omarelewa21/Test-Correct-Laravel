<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
<head>
    <meta content="text/html;charset=UTF-8" http-equiv="content-type"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <style type="text/css">

        :root {
            --system-base: #041F74;
            --blue-grey: #C3D0ED;
            --off-white: #F9FAFF;
            --cta-primary: #3DBB56;
            --cta-primary-mid-dark: #27973D;
            --cta-primary-dark: #006314;
        }

        table {
            font-family: 'Nunito', sans-serif;
        }

        h1, h2, h3, h4, h5, h6 {
            Margin: 0;
        }

        h5 {
            font-weight: 700;
            font-size: 1.25rem;

        }

        .body2 {
            font-family: 'Nunito', sans-serif;
            font-weight: 400;
            font-size: 1rem;
            color: var(--system-base);
        }

        .body2 p {
            Margin: 0;
        }

        .base {
            color: var(--system-base);
        }

        .rounded-b-10 {
            border-bottom-right-radius: 10px;
            border-bottom-left-radius: 10px;
        }
    </style>
    <style type="text/css">
        .footer {
            background-color: var(--off-white);
            border-top: 1px solid var(--blue-grey);
            border-bottom-right-radius: 10px;
            border-bottom-left-radius: 10px;
        }

        tfoot {
            color: var(--primary)
        }

        .block {
            display: block;
        }

        .button {
            border-radius: 10px;
            font-size: 18px;
            font-family: 'Nunito', sans-serif;
            font-weight: 700;
            background-color: #42b947;
            padding: 15px 30px;
            display: inline-block;
        }

        .button,
        .button * {
            color: #ffffff;
            text-decoration: none;
        }

        .button.stretched {
            display: block;
        }

        .cta-button {
            background: var(--cta-primary);
            background: linear-gradient(90deg, var(--cta-primary) 0%, var(--cta-primary) 100%);
            color: white;
        }

        .cta-button:hover {
            background: linear-gradient(90deg, rgba(39, 151, 61, 1) 0%, rgba(61, 187, 86, 1) 100%);
            box-shadow: 0 1px 18px 0 rgba(77, 87, 143, 0.5);
            transition: ease-in-out 150ms;
        }

        .cta-button:active {
            background: linear-gradient(90deg, rgba(57, 180, 81, 1) 0%, rgba(61, 187, 86, 1) 100%);
            box-shadow: 0 1px 6px 0 rgba(77, 87, 143, 0.5);
            border: none;
        }

        .cta-button:focus {
            background: var(--cta-primary);
            border: 2px solid var(--cta-primary-dark);
            padding: 13px 28px;
            outline: none;
        }

        .svg-stroke-white {
            stroke: white;
        }
    </style>
    <style type="text/css">
        .head-border {
            border: 1px solid var(--blue-grey);
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
            border-bottom: 0px;
        }

        .border-l-r {
            border: 1px solid var(--blue-grey);
            border-bottom: 0;
            border-top: 0;
        }

        .border-all {
            border: 1px solid var(--blue-grey);
        }

        .px-5 {
            padding-left: 1.25rem;
            padding-right: 1.25rem;
        }

        .p-40 {
            padding: 40px;
        }

        .pl-40 {
            padding-left: 40px;
        }

        .pr-40 {
            padding-right: 40px;
        }

        .pt-40 {
            padding-top: 40px;
        }

        .pb-40 {
            padding-bottom: 40px;
        }

        .p-20 {
            padding: 20px;
        }

        .pl-20 {
            padding-left: 20px;
        }

        .pr-20 {
            padding-right: 20px;
        }

        .pt-20 {
            padding-top: 20px;
        }

        .pb-20 {
            padding-bottom: 20px;
        }

        .p-4 {
            padding: 1rem;
        }

        .pl-4 {
            padding-left: 1rem;
        }

        .pr-4 {
            padding-right: 1rem;
        }

        .pt-4 {
            padding-top: 1rem;
        }

        .pb-4 {
            padding-bottom: 1rem;
        }

        .m-4 {
            Margin: 1rem;
        }

        .mt-4 {
            Margin-top: 1rem;
        }

        .mb-4 {
            Margin-bottom: 1rem;
        }

        .ml-4 {
            Margin-left: 1rem
        }

        .mr-4 {
            Margin-right: 1rem;
        }

        .mt-40 {
            Margin-top: 40px;
        }

        .mb-40 {
            Margin-bottom: 40px;
        }

        .ml-40 {
            Margin-left: 40px;
        }

        .mr-40 {
            Margin-right: 40px;
        }

        .td-img {
            width: 64px;
        }

        .td-text {
            width: 255px;
        }

        root,
        html,
        body {
            min-width: 100%;
            width: 100%;
            min-height: 100%;
            height: 100%;
            Margin: 0;
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
            font-weight: 400;
        }

        body h1 {
            font-size: 24px;
            Margin-bottom: 30px;
        }

        body h1:last-child,
        body p:last-child,
        body .button:last-child {
            Margin-bottom: 0px;
        }

        table,
        table thead,
        table thead tr,
        table thead tr th,
        table tfoot,
        table tfoot tr,
        table tfoot tr td {
            border-spacing: 0px !important;
            -webkit-border-horizontal-spacing: 0px !important;
            -webkit-border-vertical-spacing: 0px !important;
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

        .text-v-top {
            vertical-align: top;
        }

        .text-center {
            text-align: center;
        }

        .text-regular {
            font-weight: 400;
        }

        .text-bold {
            font-weight: 700;
        }

        .text-myriad {
            font-family: 'Myriad Pro', 'Arial', sans-serif;
            font-weight: 200;
        }

        .padding-top {
            padding-top: 30px !important;
        }

        .padding-right {
            padding-right: 30px !important;
        }

        .padding-bottom {
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

        .table.full {
            width: 100%;
        }

        .table th:last-child,
        .table td:last-child {
            border-right: none;
        }

        td {
            line-height: 22px;
        }

        #wrapper {
            max-width: 720px;
            Margin-left: auto;
            Margin-right: auto;
            Margin-top: 50px;
            Margin-bottom: 50px;
        }

        #wrapper #header {
        }

        #wrapper #header * {
            color: #ffffff;
        }

        #wrapper #header th {
            height: auto;
            Margin: 0px;
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
            Margin: 0px;

            font-size: 15px;
        }

        #wrapper #footer td h1 {
            Margin: 0px;

            font-size: 24px;
        }

        #wrapper #footer #helper {
            background-size: cover;
            background-position: center center;
            width: 75px;
            height: 75px;

            border-radius: 50%;
            -o-border-radius: 50%;
            -ms-border-radius: 50%;
            -moz-border-radius: 50%;
            -webkit-border-radius: 50%;
        }

        #footer {
            color: #555555;
            font-size: 14px;
        }

        #footer a {
            color: #333333;
            font-size: 14px !important;
        }


    </style>
</head>

<body class="pt-40" leftMargin="0" topMargin="0" Marginwidth="0" Marginheight="0"
      style="word-wrap: break-word; -webkit-nbsp-mode: space; -webkit-line-break: after-white-space;Margin:0 auto;">

<table id="wrapper" border="0" width="720px" cellpadding="0" cellspacing="0"
       style="background:white; width: 720px; Margin-left: auto; Margin-right: auto; Margin: 0px auto;">
    <thead id="header">
    <tr>
        <th colspan="999" class="pt-20 pb-20 head-border">
            <img width="274" height="50" id="logo"
                 src="{{config('app.base_url')}}img/Logo-Test-Correct-recolored.png"
                 alt="Test-Correct"/>
        </td>
    </tr>
    </thead>
    <tbody id="content" class="body2">
    @yield('content')
    </tbody>
    <tfoot class="body2">
    @yield('tfoot')
    </tfoot>
</table>
</body>
</html>