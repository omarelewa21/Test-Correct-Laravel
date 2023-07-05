<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <title>Test-Correct onderhoud</title>

    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="viewport" content="width=1280, user-scalable = no">

    <link rel="icon" href="https://www.test-correct.nl/wp-content/uploads/2019/01/cropped-fav-32x32.png" sizes="32x32" />
    <link rel="icon" href="https://www.test-correct.nl/wp-content/uploads/2019/01/cropped-fav-192x192.png" sizes="192x192" />
    <link rel="apple-touch-icon-precomposed" href="https://www.test-correct.nl/wp-content/uploads/2019/01/cropped-fav-180x180.png" />
    <meta name="msapplication-TileImage" content="https://www.test-correct.nl/wp-content/uploads/2019/01/cropped-fav-270x270.png" />

    <style>
        body {
            margin:         0px;
            font-family:    'Myriad Pro', 'Arial';
        }
        #container {
            margin-top:     92px;
            padding:        25px;
            height:         500px;
            position:       absolute;
            width:          100%;
        }
        .block {
            margin-bottom:  20px;
            box-shadow:     0px 1px 3px rgba(0, 0, 0, 0.2);
            background:     white;
        }
        .m56 {
            margin: 56px;
        }
        #background {
            position:       fixed;
            background:     #f5f5f5;
            left:           0px;
            top:            0px;
            width:          100%;
            height:         100%;
            z-index:        0;
        }
    </style>
</head>

<body class="ck-content ">

<div id="background"></div>


<div id="container">
    <div class="block" style="background-color: #ff6666;max-width:650px;margin:auto">
        <div class="m56" style="margin-top:75px;padding:15px 15px 25px 15px">
            {!! $deployment->content !!}
        </div>
    </div>
</div>
</body>
</html>

