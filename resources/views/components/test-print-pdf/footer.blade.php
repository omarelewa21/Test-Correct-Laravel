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
            {{-- adding 'Bronvermelding' makes all footers larger... this is a problem --}}
            // if (vars['page'] == vars['topage']) {
            //     document.getElementById('extraFooterLine').classList.remove('hidden');
            //     document.getElementById('extraFooterTable').classList.remove('hidden');
            // }

            document.getElementById('continue-reading').textContent = vars['page'] == vars['topage'] ? 'einde.' : 'lees verder';
        }
    </script>
    <link rel="stylesheet" href="file://{{ public_path('/css/app_pdf.css') }}">
    <link rel="stylesheet" href="file://{{ public_path('/css/print-test-pdf.css') }}">
</head>
<body class="test-print-pdf" style="border:0; margin: 0;" onload="subst()">
{{-- adding 'Bronvermelding' makes all footers larger... this is a problem --}}

{{--<div id="extraFooterLine" class="footer-line" style=""></div> --}}
{{--<table id="extraFooterTable" class="hidden">--}}
{{--    <tr>--}}
{{--        <th >--}}
{{--            {{ __('Bronvermelding') }}--}}
{{--        </th>--}}
{{--    </tr>--}}
{{--    <tr>--}}
{{--        <td>--}}
{{--            {{ __('Bronvermelding_text') }}--}}
{{--        </td>--}}
{{--    </tr>--}}
{{--</table>--}}
<span style="font-size: 7px">&nbsp;</span>
<div class="footer-line"></div>
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
            <span class="sitepage"></span> / <span class="sitepages"></span>
        </td>
        <td style="text-align: right; width: 33%">
            <span id="continue-reading"></span>
        </td>
    </tr>
</table>
</body></html>