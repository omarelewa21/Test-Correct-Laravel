<header id="header" class="header flex flex-wrap content-center">
    <a class="mr-4" href="{{\tcCore\Http\Helpers\BaseHelper::getLoginUrl()}}">
        <img class="" src="/svg/logos/Logo-Test-Correct-2.svg"
             alt="Test-Correct">
    </a>
    <div id="menu" class="menu flex flex-wrap content-center">
        <div class="menu-item px-2"><a href="#" class="text-button">{{ __("header.Dashboard") }}</a></div>
        <div class="menu-item px-2"><a href="#" class="text-button active">{{ __("header.Toetsing") }}</a></div>
        <div class="menu-item px-2"><a href="#" class="text-button">{{ __("header.Analyses") }}</a></div>
        <div class="menu-item px-2"><a href="#" class="text-button">{{ __("header.Berichten") }}</a></div>
        <div class="menu-item px-2"><a href="#" class="text-button">{{ __("header.Kennisbank") }}</a></div>
    </div>
    <div class="user flex flex-wrap items-center ml-auto">
        <a href="#" class="question rounded-full flex items-center justify-center" style="width:30px;height:30px">
            <x-icon.questionmark color="white"></x-icon.questionmark>
        </a>
        <a href="#" class="text-button ml-4 font-size-18 rotate-svg-90">{ {{ __("header.account naam") }} }
            <x-icon.chevron></x-icon.chevron>
        </a>
    </div>
</header>
<div class="sub-menu flex px-28">
    <div class="Toetsing menu flex flex-wrap content-center">
        <div class="px-3"><a href="#" class="text-button">{{ __("header.Gepland") }}</a></div>
        <div class="px-3"><a href="#" class="text-button">{{ __("header.CO-Learning") }}</a></div>
        <div class="px-3"><a href="#" class="text-button">{{ __("header.Inzien") }}</a></div>
        <div class="px-3"><a href="#" class="text-button">{{ __("header.Becijferd") }}</a></div>
    </div>
</div>

