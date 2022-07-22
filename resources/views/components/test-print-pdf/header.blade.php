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
</head>
<body class="test-print-pdf" style="border:0; margin: 0;" onload="subst()">
<table class="header-table" style="width: 100%;">
    <tr>
        <td class="doctitle bold"></td>
        <td class="" style="text-align:right">
            Toets HAVO
        </td>
    </tr>
    <tr>
        <td class="bold">
            Biologie
        </td>
        <td class="bold text-right">
            2022
        </td>
    </tr>
</table>
<div class="header-line"></div>
</body></html>