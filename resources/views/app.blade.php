<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="UTF-8">

    <link rel="icon" href="https://www.test-correct.nl/wp-content/uploads/2019/01/cropped-fav-32x32.png" sizes="32x32" />
    <link rel="icon" href="https://www.test-correct.nl/wp-content/uploads/2019/01/cropped-fav-192x192.png" sizes="192x192" />
    <title>Test-Correct</title>
    <!-- Tailwind CSS -->
    <link href="https://unpkg.com/tailwindcss@^2/dist/tailwind.min.css" rel="stylesheet">

    <!-- Alpine -->
    <script src="https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.x.x/dist/alpine.min.js" defer></script>

    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/pikaday/css/pikaday.css">
    <link rel="stylesheet" type="text/css" href="https://unpkg.com/trix@1.2.3/dist/trix.css">
    <link rel="stylesheet" href="/css/onboarding.css">

</head>

<body class="ck-content bg-light-grey">
<header class="py-5 bg-white onboarding-header">
    <div class="max-w-2xl mx-auto grid grid-cols-3 gap-y-4 mid-grey">
        <div class="col-span-3">
            <a class="mx-auto tc-logo block" href="{{\tcCore\Http\Helpers\BaseHelper::getLoginUrl()}}">
                <img class="" src="/svg/logos/Logo-Test-Correct-recolored.svg"
                     alt="Test-Correct">
            </a>
        </div>
    </div>
</header>

@yield('content')

</body>
</html>
