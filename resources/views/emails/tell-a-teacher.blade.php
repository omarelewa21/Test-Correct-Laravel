<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
<head>
    <meta content="text/html;charset=UTF-8" http-equiv="content-type"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>

    <style type="text/css">
        /* ----- Client Fixes ----- */
        /* Force Outlook to provide a “view in browser” message */
        #outlook a {
            padding: 0;
        }

        /* Force Hotmail to display emails at full width */
        .ReadMsgBody {
            width: 100%;
        }

        .ExternalClass {
            width: 100%;
        }

        /* Force Hotmail to display normal line spacing */
        .ExternalClass,
        .ExternalClass p,
        .ExternalClass span,
        .ExternalClass font,
        .ExternalClass td,
        .ExternalClass div {
            line-height: 100%;
        }

        /* Prevent WebKit and Windows mobile changing default text sizes */
        body, table, td, p, a, li, blockquote {
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
        }

        /* Remove spacing between tables in Outlook 2007 and up */
        table, td {
            mso-table-lspace: 0pt;
            mso-table-rspace: 0pt;
        }

        /* Allow smoother rendering of resized image in Internet Explorer */
        img {
            -ms-interpolation-mode: bicubic;
        }

        :root {
            --system-base: #041F74;
            --blue-grey: #C3D0ED;
            --off-white: #F9FAFF;
            --cta-primary: #3DBB56;
            --cta-primary-mid-dark: #27973D;
            --cta-primary-dark: #006314;
            --bg: #e1e4e9;
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

        .button,
        .button * {
            color: #ffffff;
            text-decoration: none;
        }

        .p-40 {
            padding: 40px;
        }

        .pt-40 {
            padding-top: 40px;
        }
        .pb-40 {
            padding-bottom: 40px;
        }

        .pt-20 {
            padding-top: 20px;
        }

        .pb-20 {
            padding-bottom: 20px;
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
            box-sizing: border-box;
            font-weight: 400;
        }

        body h1 {
            font-size: 24px;
            Margin-bottom: 30px;
        }
    </style>
</head>
<body>
<div class="pt-40" leftMargin="0" topMargin="0" Marginwidth="0" Marginheight="0" bgcolor="#e1e4e9"
     style="word-wrap: break-word; -webkit-nbsp-mode: space; -webkit-line-break: after-white-space;Margin:0 auto; background-color:#e1e4e9">
    <div>
        <table id="wrapper" border="0" width="720px" cellpadding="0" cellspacing="0" bgcolor="#e1e4e9"
               style="width: 720px; Margin-left: auto; Margin-right: auto; Margin: 0px auto;border-top-left-radius: 5px;border-top-right-radius: 5px;">
            <thead id="header"
                   style="background-color: #004df5;padding-top: 40px;padding-left: 40px;padding-right: 40px;padding-bottom:40px;">
            <tr>
                <td style="border: 1px solid var(--blue-grey);border-top-left-radius: 10px;border-top-right-radius: 10px;border-bottom: 0px;"
                    width="720" border="0" cellspacing="0" cellpadding="0" valign="top">
                    <div>
                        <table style="width: 100%; color: #ffffff">
                            <tr style="Margin-right: 0;Margin-left: 0;Margin:0 auto">
                                <th colspan="999" class="pt-20 pb-20 head-border"
                                    style="Margin-right: 0;Margin-left: 0;Margin:0 auto;padding-left:40px;padding-right:40px">
                                    <img width="164" height="30" id="logo"
                                         style="Margin-right: 0;Margin-left: 0;Margin:0 auto"
                                         src="{{config('app.base_url')}}img/Logo-Test-Correct-wit.png"
                                         alt="Test-Correct"/>
                                </td>
                            </tr>
                            <tr width="100%">
                                <td width="600px" style="width:600px;padding-left: 40px;padding-right: 40px;padding-bottom: 40px">
                                    <h5 style="color: #fff; font-size: 20px; font-weight:700; padding-bottom: 16px">{{ __("tell-a-teacher.Je collega") }} {{$inviter}} {{ __("tell-a-teacher.nodigt je uit voor Test-Correct") }}</h5>
                                    <table width="600px">
                                        <tr>
                                            <td style="width: 100%; color: #ffffff">{{ __("tell-a-teacher.Samen met je collega's kun je") }}:</td>
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
                                                        <td style="color: #ffffff">
                                                    <span style="margin-bottom: 8px">{{ __("tell-a-teacher.Overleggen over de voortgang van jouw studenten en ervaringen delen") }}.</span>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>
                                                            <img width="16"
                                                                 src="{{config('app.base_url')}}img/icons/checkmark-small-white.png"
                                                                 alt=""/>
                                                        </td>
                                                        <td style="color: #ffffff"><span>{{ __("tell-a-teacher.Gebruikmaken van elkaars toetsen en toetsvragen") }}.</span>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </div>
                </td>
            </tr>
            </thead>
        </table>
    <table border="0" width="720px" cellpadding="0" cellspacing="0" bgcolor="#e1e4e9"
           style="width: 720px;Margin-left: auto;Margin-right: auto;Margin: 0px auto;border-bottom-left-radius:5px;border-bottom-right-radius: 5px;background-color:#e1e4e9;padding-bottom:40px">
        <tbody id="content" class="body2" bgcolor="#e1e4e9" style="width:720px;background-color:#e1e4e9">
        <tr bgcolor="#e1e4e9"
            style="border-collapse:collapse; border-radius:4px;border-color: white; border-style: solid; border-width: 1px;background-color:#e1e4e9">
            <td bgcolor="#e1e4e9" colspan="999" class="p-40 border-l-r" style="color: #041F74;
            border: 1px solid var(--blue-grey);
            border-bottom-left-radius: 10px;
            border-bottom-right-radius: 10px;
            border-top: 0px;
            background-color: #ffffff;">
                <p style="color: #041F74;font-size: 16px;Margin-bottom: 20px">{{ $inviteText }}</p>
                <table width="100%" border="0" cellspacing="0" cellpadding="0">
                    <tr>
                        <td>
                            <table border="0" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td bgcolor="#3DBB56" style="padding: 12px 18px 12px 18px; border-radius:5px"
                                        align="center"><a
                                                href="{{ config('app.base_url')}}inv/{{$shortcode}}?email={{$receivingEmailAddress}}"
                                                target="_blank"
                                                style="font-size: 16px; font-family: Helvetica, Arial, sans-serif; font-weight: normal; color: #ffffff; text-decoration: none; display: inline-block;">{{ __("tell-a-teacher.Maak jouw account aan") }}</a></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        </tbody>
    </table>
</div>
</body>
</html>

