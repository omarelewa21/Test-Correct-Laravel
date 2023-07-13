<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
<head>
    <meta content="text/html;charset=UTF-8" http-equiv="content-type" />

    <link href='http://fonts.googleapis.com/css?family=Open+Sans:400,600,300' rel='stylesheet' type='text/css'>
    <link href='http://maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css' rel='stylesheet' type='text/css'>

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
            background-color: #113b50;
        }

        body * {
            margin: 0px;
            padding: 0px;
            font-size: 15px;
            font-family: 'Open Sans', sans-serif;
            font-weight: 300;
            color: #0a2431;
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

        table,
        table thead,
        table thead tr,
        table thead tr th,
        table tfoot,
        table tfoot tr,
        table tfoot tr td {
            border-spacing: 0px !important;
            border-collapse: collapse !important;
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
            max-width: 510px;
            margin-left: auto;
            margin-right: auto;
            margin-top: 50px;
            margin-bottom: 50px;

            border-radius: 5px;
            -o-border-radius: 5px;
            -ms-border-radius: 5px;
            -moz-border-radius: 5px;
            -webkit-border-radius: 5px;

            overflow: hidden;
        }

        #wrapper #header {
            background-color: #f9f9f9;
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

<body class="ck-content" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0" style="word-wrap: break-word; -webkit-nbsp-mode: space; -webkit-line-break: after-white-space;">

<table id="wrapper" border="0" width="500px" cellpadding="0" cellspacing="0" style="background:white; width: 500px; margin-left: auto; margin-right: auto; margin: 0px auto;">
   <thead id="header">
    <tr>
        <th colspan="999" class="text-center text-regular padding-top padding-right padding-bottom padding-left">
            <img width="265" id="logo" src="http://testportal.test-correct.nl/img/logo_full.png"/>
        </td>
    </tr>
    </thead>
    <tbody id="content" style="font-family: Arial; font-size: 15px;">
        @yield('content')
    </tbody>
</table>
</body>
</html>