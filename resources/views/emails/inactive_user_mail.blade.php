@extends('emails.onboarding-layout')

@section('content')
    <tr>
        <td colspan="999" class="pl-40 pr-40 pb-40 border-l-r" style="color: #041F74;">
            <h5 class="mb-4">Welkom bij Test-Correct</h5>

            <p>{{__('inactive-user-mail.Beste') }} {{ $user->getFormalNameAttribute() }},</p>
            <br>
            <p>{{__('inactive-user-mail.Je hebt het afgelopen jaar niet ingelogd in Test-Correct.')}}</p>
            <p>{{__('inactive-user-mail.Als je geen actie onderneemt, dan zal jouw account verwijderd worden.') }}</p>
            <p>{{__('inactive-user-mail.Wil je jouw account behouden? Log dan in.') }}</p>

            <p>
                <a style="border-radius: 10px;
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
                class="mt-40 button cta-button stretched text-center svg-stroke-white" href="{{ config('app.url_login') }}">{{__('onboarding-welcome.Klik hier om in te loggen')}}
                    <x-icon.arrow></x-icon.arrow>
                </a>
                <br/>
            </p>

        </td>
    </tr>
    <tr class="footer rounded-b-10" style="background: #F9FAFF; color: #041F74">
        <td class="p-40 rounded-b-10 border-all">

        </td>
    </tr>

@stop
