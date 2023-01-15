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

            // document.getElementById('continue-reading').textContent = vars['page'] == vars['topage'] ? 'einde.' : 'lees verder';
            if(vars['page'] == vars['topage']) {
                document.getElementById('end-of-document').classList.remove('hidden')
            } else {
                document.getElementById('continue-reading').classList.remove('hidden')
            }
        }
    </script>
    <link rel="stylesheet" href="file://{{ public_path('/css/app_pdf.css') }}">
    <link rel="stylesheet" href="file://{{ public_path('/css/print-test-pdf.css') }}">
</head>
<body class="test-print-pdf {{ $extraCssClass }}" style="border:0; margin: 0;" onload="subst()">

<span style="font-size: 7px">&nbsp;</span>
<div class="footer-line"></div>
<table class="footer-table" style="border:0; width: 100%;height: 20px; border-color: #ffffff">
    <tr>
        <td colspan="3" style="width: 10em">
        </td>
    </tr>
    <tr>
        <td style="text-align: left; width: 33%; ">
            <span id="Title" > {{ $test->name }} </span>
        </td>
        <td style="text-align: center; width: 33%">
            <span class="sitepage"></span> / <span class="sitepages"></span>
        </td>
        <td class="bold" style="text-align: right; width: 33%">
            <span class="hidden" id="continue-reading">
                {{ __('test-pdf.continue-reading') }}
                <svg class="footer-icon-continue" width="9" height="13" xmlns="http://www.w3.org/2000/svg">
                    <path stroke="currentColor" stroke-width="3" d="M1.5 1.5l5 5-5 5" fill="none" fill-rule="evenodd"
                          stroke-linecap="round"/>
                </svg>
            </span>
            <span class="hidden" id="end-of-document">
                {{ __('test-pdf.end-of-document') }}
                <span class="footer-icon-end"></span>
            </span>
        </td>
    </tr>
</table>
</body></html>