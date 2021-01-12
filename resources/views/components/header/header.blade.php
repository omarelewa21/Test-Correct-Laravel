<header class="header flex flex-wrap content-center">
    <a class="mr-4" href="{{config('app.url_login')}}">
        <img class="" src="/svg/logos/Logo-Test-Correct-2.svg"
             alt="Test-Correct">
    </a>
    <div id="menu" class="menu flex flex-wrap content-center">
        <div class="menu-item px-2"><a href="#" class="text-button">Dashboard</a></div>
        <div class="menu-item px-2"><a href="#" class="text-button active">Toetsing</a></div>
        <div class="menu-item px-2"><a href="#" class="text-button">Analyses</a></div>
        <div class="menu-item px-2"><a href="#" class="text-button">Berichten</a></div>
        <div class="menu-item px-2"><a href="#" class="text-button">Kennisbank</a></div>
    </div>
    <div class="user flex flex-wrap items-center ml-auto">
        <a href="#" class="question rounded-full flex items-center justify-center" style="width:30px;height:30px">
            <x-icon.questionmark color="white"></x-icon.questionmark>
        </a>
        <a href="#" class="text-button ml-4 font-size-18 rotate-svg-90">{ account naam }
            <x-icon.chevron></x-icon.chevron>
        </a>
    </div>
</header>
<div class="sub-menu flex px-28">
    <div class="Toetsing menu flex flex-wrap content-center">
        <div class="px-3"><a href="#" class="text-button">Gepland</a></div>
        <div class="px-3"><a href="#" class="text-button">Bespreken</a></div>
        <div class="px-3"><a href="#" class="text-button">Inzien</a></div>
        <div class="px-3"><a href="#" class="text-button">Becijferd</a></div>
    </div>
</div>

