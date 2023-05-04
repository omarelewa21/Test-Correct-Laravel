<!DOCTYPE html>
<html><head><script>
        function subst() {
            var vars = {};
            var query_strings_from_url = document.location.search.substring(1).split('&');
            for (var query_string in query_strings_from_url) {
                if (query_strings_from_url.hasOwnProperty(query_string)) {
                    var temp_var = query_strings_from_url[query_string].split('=', 2);
                    vars[temp_var[0]] = decodeURI(temp_var[1]);
                }
            }
            var css_selector_classes = ['page', 'frompage', 'topage', 'webpage', 'section', 'subsection', 'date', 'isodate', 'time', 'title', 'doctitle', 'sitepage', 'sitepages'];
            for (var css_class in css_selector_classes) {
                if (css_selector_classes.hasOwnProperty(css_class)) {
                    var element = document.getElementsByClassName(css_selector_classes[css_class]);
                    for (var j = 0; j < element.length; ++j) {
                        element[j].textContent = vars[css_selector_classes[css_class]];
                    }
                }
            }
            // const myFont = new FontFace('Nunito', 'url(/fonts/Nunito-VariableFont_wght.ttf)');
            // await myFont.load();
            // document.fonts.add(myFont);
            //
            // document.getElementsByClassName('header-text')[0].style.fontFamily = "Nunito";
        }
    </script>
    <link rel="stylesheet" href="file://{{ public_path('/css/app_pdf.css') }}">
    <link rel="stylesheet" href="file://{{ public_path('/css/print-test-pdf.css') }}">
</head>
<body class="test-print-pdf {{ $extraCssClass }}" style="border:0; margin: 0;" onload="subst()">
<table class="header-table" style="width: 100%; border: none !important; border-color: #ffffff;">
    <tr style="border: none !important; border-color: #ffffff;">
        <td class=" bold" style="border: none !important; border-color: #ffffff;">
            {{ $test->name }}
        </td>
        <td class="" style="text-align:right">
            {{ __("test-pdf.".$testType) }} {{ $test->educationLevel->name }}
        </td>
    </tr>
    <tr style="border: none !important; border-color: #ffffff;">
        <td class="bold" style="border: none !important; border-color: #ffffff;">
            {{ $test->subject->name }}
        </td>
        <td class="bold text-right" style="border: none !important; border-color: #ffffff;">
            {{ \tcCore\Lib\Repositories\PeriodRepository::getCurrentPeriod()->schoolYear->year }}
        </td>
    </tr>
</table>
<div class="header-line"></div>
<span style="font-size: 4px; line-height: 4px;">&nbsp;</span>
</body></html>