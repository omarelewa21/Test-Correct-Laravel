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
            // const myFont = new FontFace('Nunito Bold', 'url(/fonts/nunito-bold.tff)');
            // await myFont.load();
            // document.fonts.add(myFont);
            //
            // document.getElementsByClassName('header-text')[0].style.fontFamily = "Nunito Bold";
        }
    </script>
    <link rel="stylesheet" href="file://{{ public_path('/css/app_pdf.css') }}">
    <link rel="stylesheet" href="file://{{ public_path('/css/print-test-pdf.css') }}">
</head>
<body class="test-print-pdf" style="border:0; margin: 0;" onload="subst()">
<table class="header-table" style="width: 100%;">
    <tr>
        <td class=" bold">
            {{ $test->name }}
        </td>
        <td class="" style="text-align:right">
            {{__('test.toets')}} {{ $test->educationLevel->name }}
        </td>
    </tr>
    <tr>
        <td class="bold">
            {{ $test->subject->name }}
        </td>
        <td class="bold text-right">
            {{ $test->period->schoolYear->year }}
        </td>
    </tr>
</table>
<div class="header-line"></div>
<span style="font-size: 4px; line-height: 4px;">&nbsp;</span>
</body></html>