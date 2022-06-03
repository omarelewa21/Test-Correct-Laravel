@extends('emails.onboarding-layout')

@section('content')
    <tr>
        <td colspan="999" class="pl-40 pr-40 pb-40 border-l-r" style="color: #041F74;">
            <h5 class="mb-4">{{ __("onboarding-welcome.Welkom bij Test-Correct") }}</h5>
            @if(!$skipVerificationPart)
            <p>{{ __("onboarding-welcome.Je hebt je aangemeld met het e-mailadres") }} <span class="text-bold">{{$user->username}}</span></p>
            <p>{{ __("onboarding-welcome.Verifieer je e-mailadres") }}</p>
            <a href="{{ config('app.base_url'). 'user/confirm_email/'.$token}}"
               style="border-radius: 10px;
            font-size: 18px;
            font-family: 'Nunito', sans-serif;
            font-weight: 700;
            background-color: #42b947;
            Padding: 15px 30px;
            display: block;
            background: var(--cta-primary);
            color: var(--system-base);
            text-decoration: underline;
            text-align: center;
            stroke: var(--system-base);
            Margin-top: 40px;
            "
               class="mt-40 button cta-button stretched text-center svg-stroke-white"> {{__('onboarding-welcome.Verifieer je e-mailadres')}}
                <x-icon.arrow></x-icon.arrow>
            </a>
            @else
                <p>{{ __("onboarding-welcome.Je hebt je aangemeld via Entree") }}</p>
                <p>
                    <a href="{{ config('app.url_login') }}">{{__('onboarding-welcome.Klik hier om in te loggen')}}</a><br/>
                </p>
            @endif
        </td>
    </tr>
    <tr class="footer rounded-b-10" style="background: #F9FAFF; color: #041F74">
        <td class="p-40 rounded-b-10 border-all">
            <h5 class="mb-4">{{ __("onboarding-welcome.Werk volledig digitaal") }}...</h5>
            <table>
                <tr class="mb-4">
                    <td class="td-img pb-4">
                        <img width="64" height="64"
                             src="{{config('app.base_url')}}img/toetsen-maken-afnemen.png"
                             alt="{{ __("onboarding-welcome.Toetsen aanmaken en bestaande toetsen omzetten") }}.">
                    </td>
                    <td class="td-text px-5">
                        <span class="text-v-top">{{ __("onboarding-welcome.Toetsen aanmaken en bestaande toetsen omzetten") }}.</span>
                    </td>
                    <td class="td-img">
                        <img width="64" height="64"
                             src="{{config('app.base_url')}}img/toetsen-beoordelen-bespreken_1.png"
                             alt="{{ __("onboarding-welcome.Toetsen beoordelen en samen de CO-Learning doorlopen") }}">
                    </td>
                    <td class="td-text px-5">
                        <span class="text-v-top">{{ __("onboarding-welcome.Toetsen beoordelen en samen de CO-Learning doorlopen") }}</span>
                    </td>
                </tr>
                <tr>
                    <td class="td-img">
                        <img width="64" height="64"
                             src="{{config('app.base_url')}}img/klassen_1.png"
                             alt="{{ __("onboarding-welcome.Klassen maken en uitnodigen om een toets af te nemen") }}">
                    </td>
                    <td class="td-text px-5">
                        <span class="text-v-top">{{ __("onboarding-welcome.Klassen maken en uitnodigen om een toets af te nemen") }}</span>
                    </td>
                    <td class="td-img">
                        <img width="64" height="64"
                             src="{{config('app.base_url')}}img/toetsresultaten-analyse.png"
                             alt="{{ __("onboarding-welcome.Toetsresultaten delen en analystische feedback inzien") }}">
                    </td>
                    <td class="td-text px-5">
                        <span class="text-v-top">{{ __("onboarding-welcome.Toetsresultaten delen en analystische feedback inzien") }}</span>
                    </td>
                </tr>
            </table>
        </td>
    </tr>

@stop
