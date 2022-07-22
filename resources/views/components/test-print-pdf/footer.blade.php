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
            if (vars['page'] != vars['topage']) {
                document.getElementById('extraFooterLine').classList.add('hidden');
                document.getElementById('extraFooterTable').classList.add('hidden');
            }
            // document.getElementById('hello3').textContent = vars[0];
            // document.getElementById('hello4').textContent = vars['sitepage'];
            document.getElementById('continue-reading').textContent = vars['page'] == vars['topage'] ? 'einde.' : 'lees verder';
        }
    </script>
    <link rel="stylesheet" href="file://{{ public_path('/css/app_pdf.css') }}">
    <style>
        @font-face {
            font-family: "Nunito Bold";
            src: url("file:{{base_path()}}/resources/fonts/Nunito/Nunito-Bold.ttf") format('truetype');
            font-weight: bold;
        }
        @font-face {
            font-family: Nunito Regular;
            src: url("file:{{base_path()}}/resources/fonts/Nunito/Nunito-Regular.ttf") format('truetype');
            font-weight: normal;
        }
    </style>
</head>
<body class="test-print-pdf" style="border:0; margin: 0;" onload="subst()">
<div id="extraFooterLine" class="footer-line" style=""></div>
<table id="extraFooterTable">
    <tr>
        <th >
            {{ __('Bronvermelding') }}
        </th>
    </tr>
    <tr>
        <td>
            {{ __('Bronvermelding_text') }}
        </td>
    </tr>
</table>
<div class="footer-line" style=""></div>
<table style="border:0; width: 100%;height: 20px; ">
    <tr>
        <td colspan="3" style="width: 10em">
        </td>
    </tr>
    <tr>
        <td style="text-align: left; width: 33%; ">
            <span id="Title" > {{ $title }} </span>
        </td>
        <td style="text-align: center; width: 33%">
            <span class="page"></span> / <span class="topage"></span>
        </td>
        <td style="text-align: right; width: 33%">
            <span id="continue-reading"></span>
        </td>
    </tr>
</table>
</body></html>