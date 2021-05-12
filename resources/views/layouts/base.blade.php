<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="UTF-8">
    <title>Test-Correct</title>
    <link rel="icon" href="https://www.test-correct.nl/wp-content/uploads/2019/01/cropped-fav-192x192.png"
          sizes="192x192"/>
    {{--    <link href="https://unpkg.com/tailwindcss@^2/dist/tailwind.min.css" rel="stylesheet">--}}
    <script src="/ckeditor/ckeditor.js" type="text/javascript"></script>

    @livewireStyles
    <link rel="stylesheet" href="{{ mix('/css/app.css') }}">
</head>
<body id="body" class="flex flex-col min-h-screen" onload="addIdsToQuestionHtml()">
{{ $slot }}

@livewireScripts
<script>
    window.livewire.onError(statusCode => {

        if (statusCode === 406) {
            console.log('in onError')
            // var element = document.querySelector('[testtakemanager]');
            // console.log(element)
            // element.dispatchEvent(new CustomEvent('set-force-taken-away', {w: true}));
            Livewire.emit('set-force-taken-away');

            return false;
        }
        if (statusCode === 440) {
            location.href = '{{ config('app.url_login') }}';

            return false
        }
    })

    function addIdsToQuestionHtml() {
        let id = 1;
        let questionContainers = document.querySelectorAll('[questionHtml]');

        questionContainers.forEach(function (item) {
            let decendents = item.querySelectorAll('*');
            decendents.forEach(function (decendent) {
                decendent.id = 'questionhtml_'+id;
                id += 1;
            })
        })


    }
    function addRelativePaddingToBody(elementId, extraPadding = 0) {
        document.getElementById(elementId).style.paddingTop = (document.getElementById('header').offsetHeight + extraPadding) + 'px';
    }
    function makeHeaderMenuActive(elementId) {
        document.getElementById(elementId).classList.add('active');
    }
</script>
<script src="{{ mix('/js/app.js') }}"></script>
<script src="https://www.wiris.net/demo/plugins/app/WIRISplugins.js?viewer=image"></script>
@stack('scripts')
</body>
</html>
