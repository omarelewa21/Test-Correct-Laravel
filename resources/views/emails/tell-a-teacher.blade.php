<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
<head>
    <meta content="text/html;charset=UTF-8" http-equiv="content-type"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>

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

        .footer {
            background-color: var(--off-white);
            border-top: 1px solid var(--blue-grey);
            border-bottom-right-radius: 10px;
            border-bottom-left-radius: 10px;
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
            color: white;
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

        .text-center {
            text-align: center;
        }

        .text-regular {
            font-weight: 400;
        }

        .text-bold {
            font-weight: 700;
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

    </style>
</head>

<body class="pt-40" leftMargin="0" topMargin="0" Marginwidth="0" Marginheight="0"
      style="word-wrap: break-word; -webkit-nbsp-mode: space; -webkit-line-break: after-white-space;Margin:0 auto; background-color:#F9FAFF">
<table id="wrapper" border="0" width="720px" cellpadding="0" cellspacing="0"
       style="background:white; width: 720px; Margin-left: auto; Margin-right: auto; Margin: 0px auto;">
    <thead id="header" style="background-color: #004df5; padding-top: 40px;padding-left: 40px;padding-right: 40px">
    <tr>
        <td background="{{ config('app.base_url')}}img/email_bg_tell_a_teacher.png" bgcolor="#004df5"
            width="720" height="338" valign="top">
            <!--[if gte mso 9]>
            <v:rect xmlns:v="urn:schemas-microsoft-com:vml" fill="true" stroke="false"
                    style="width:720px;height:340px;">
                <v:fill type="tile" src="{{ config('app.base_url')}}img/email_bg_tell_a_teacher.png"
                        color="#004df5"/>
                <v:textbox inset="0,0,0,0">
            <![endif]-->
            <div>
                <table style="width: 100%; color: #ffffff">
                    <tr>
                        <th colspan="999" class="pt-20 pb-20 head-border">
                            <img width="164" height="30" id="logo"
                                 src="{{config('app.base_url')}}img/Logo-Test-Correct-wit.png"
                                 alt="Test-Correct"/>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding-left: 40px;padding-right: 40px">
                            <h5 style="color: #fff; font-size: 20px; font-weight:700; padding-bottom: 16px">Je
                                collega {{$inviter}} nodigt je uit voor Test-Correct</h5>
                            <table>
                                <tr>
                                    <td style="width: 100%">Samen met je collega's kun je:</td>
                                </tr>
                                <tr>
                                    <td>
                                        <table>
                                            <tr style="margin-bottom: 8px">
                                                <td style="width: 21px">
                                                    <img width="16"
                                                         src="{{config('app.base_url')}}img/icons/checkmark-small-white.png"
                                                         alt=""/>
                                                </td>
                                                <td style="width: 280px">
                                                    <span style="margin-bottom: 8px">Overleggen over de voortgang van jouw studenten en ervaringen
                                                    delen.</span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <img width="16"
                                                         src="{{config('app.base_url')}}img/icons/checkmark-small-white.png"
                                                         alt=""/>
                                                </td>
                                                <td>Gebruikmaken van elkaars toetsen en toetsvragen.</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </div>
            <!--[if gte mso 9]>
            </v:textbox>
            </v:rect>
            <![endif]-->
        </td>
    </tr>

    </thead>
    <tbody id="content" class="body2">
    <tr style="border-collapse:collapse; border-radius:4px;border-color: red; border-style: solid; border-width: 1px; background-color:#ffffff;">
        <td colspan="999" class="pl-40 pr-40 pb-40 border-l-r" style="color: #041F74;">
            <p style="color: #041F74;font-size: 16px ">{{ $inviteText }}</p>
            <a href="{{ config('app.base_url')}}onboarding?step=1&email={{$receivingEmailAddress}}&sc={{$shortcode}}"
               style="border-radius: 10px;
            font-size: 18px;
            font-family: 'Nunito', sans-serif;
            font-weight: 700;
            background-color: #42b947;
            Padding: 15px 30px;
            display: block;
            background: var(--cta-primary);
            color: white;
            text-align: center;
            stroke: white;
            Margin-top: 16px;"
               class="mt-40 button cta-button stretched text-center svg-stroke-white">Maak jouw gratis account
                <x-icon.arrow></x-icon.arrow>
            </a>
        </td>
    </tr>
    </tbody>
</table>
</body>
</html>
