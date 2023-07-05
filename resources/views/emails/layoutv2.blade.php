<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
<head>
    <meta content="text/html;charset=UTF-8" http-equiv="content-type" />
    <link href='https://fonts.googleapis.com/css?family=Nunito' rel='stylesheet'>

</head>

<body class="ck-content" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0" style="word-wrap: break-word; -webkit-nbsp-mode: space; -webkit-line-break: after-white-space; padding-left: 20px;
    padding-right: 20px;
    box-sizing: border-box;
    background-color: #ffffff;
    min-width: 100%;
    width: 100%;
    min-height: 100%;
    height: 100%;
    margin: 0;
    padding: 0;
    position: relative;
    display: block;
    overflow-y: auto;
    overflow-x: hidden;">

<table id="wrapper" border="0" width="720px" cellpadding="0" cellspacing="0" style="background:white; width: 720px; margin-left: auto; margin-right: auto; margin: 0px auto; background: white;
    width: 720px;
    margin: 0px auto;
    border-collapse: separate;
    background-color: #ffffff;
    max-width: 720px;
    margin-left: auto;
    margin-right: auto;
    margin-top: 50px;
    margin-bottom: 50px;
    overflow: hidden;">
    <tbody id="content" style="font-family: Nunito, sans-serif,Trebuchet MS,Arial; font-size: 15px;">
        <tr>
            <td colspan="999"  style="  text-align: center;
                                        font-weight: 400;
                                        padding-top: 10px;
                                        padding-right: 30px;
                                        padding-bottom: 10px;
                                        padding-left: 40px;">
                <img src="{{config('app.base_url')}}img/mail/logo-test-correct.png">
            </td>
        </tr>
        <tr  style=" background-color: #004df5;
                                        color: #ffffff;
                                        padding-top: 20px;
                                        padding-bottom: 20px;
                                        font-family: Nunito, sans-serif,Trebuchet MS,Arial;
                                        font-weight: bold;">
            <td colspan="999"  style="font-weight: 400;
                                        padding-top: 10px;
                                        padding-bottom: 10px;
                                        padding-left: 30px;
                                        padding-right: 40px;
                                        border-top-left-radius: 10px;
                                        border-top-right-radius: 10px;
                                        -moz-border-radius-topleft: 10px;
                                        -moz-border-radius-topright: 10px;
                                        -webkit-border-top-left-radius: 10px;
                                        -webkit-border-top-right-radius: 10px;
                                        margin: 0px;">
                <table  style=" border-collapse: separate;
                        width: 100%;
                        max-width: 100%;
                        margin-top: 20px;
                        margin-bottom: 30px;">
                    <tr >
                        <td style="padding-top: 10px;
                                font-size: 18px;
                                color: #ffffff;
                                padding-left: 0px;
                                padding-right: 0px;
                                margin: 0px;
                                padding-bottom: 0px;
                                font-family: Nunito, sans-serif,Trebuchet MS,Arial;
                                font-weight: 300;">
                            <span style="padding-right: 10px;"><img src="{{config('app.base_url')}}img/mail/icons-arrow-white.png"></span>
                            @yield('header_intro')
                        </td>
                    </tr>
                    <tr >
                        <td class="header_message" style="  padding-top: 10px;
                                                            font-size: 28px;
                                                            color: #ffffff;
                                                            width: 100%;
                                                            border-bottom: 3px #ffffff;
                                                            padding-left: 0px;
                                                            padding-right: 0px;
                                                            margin: 0px;
                                                            padding-bottom: 0px;
                                                            font-family: Nunito, sans-serif,Trebuchet MS,Arial;
                                                            font-weight: 300;
                                                            ">@yield('header_message')</td>
                    </tr>
                    <tr >
                        <td style=" padding-left: 0px;
                                    padding-right: 0px;
                                    width: 100%;
                                    max-width: 100%; "><hr class="header_line" style="height: 3px;
                                                            width: 100%;
                                                            max-width: 100%;
                                                            color: #ffffff;
                                                            background-color: #ffffff;
                                                            border-radius: 10px;
                                                            margin-top: 20px;
                                                            "/></td>
                    </tr>
                </table>
            </td>
        </tr>
        @yield('content')
        @yield('support')
        @yield('footer')
        <tr >
            <td colspan="999" style="padding-left: 100px;padding-right: 40px;padding-top: 30px;">
                <p style="font-size: 14px;
                        line-height: 24px;
                        font-family: Nunito, sans-serif,Trebuchet MS,Arial;
                        color: #6b7789;
                        font-size: 15px;
                        margin-bottom: 15px;">www.test-correct.nl, Dotterbloemstraat 25, 3053 JV, Rotterdam, Nederland</p>
                <p style="font-size: 14px;
                        line-height: 24px;
                        font-family: Nunito, sans-serif,Trebuchet MS,Arial;
                        color: #6b7789;
                        margin-bottom: 15px;">Je kunt je niet afmelden voor belangrijke e-mails over je account, zoals deze.</p>
            </td>
        </tr>
    </tbody>

</table>
</body>
</html>