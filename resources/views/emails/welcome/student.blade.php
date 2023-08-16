@extends('emails.layout')

@section('content')
    <tr>
        <td colspan="999" style="padding:20px;">
            {{ __("student.Beste") }} {{ $user->name_first }},<br/>
            <br/>
            {{ __("student.Op het verzoek van jouw school sturen we hierbij je inloggegevens van Test-Correct. Hiermee kun je inloggen om toetsen te maken") }}. <strong> {{__("student.Lees de onderstaande punten goed door voordat je de applicatie gaat downloaden") }}:</strong>
            <ul>
                <li>{{ __("student.Om een toets in Test-Correct te maken, moet je de applicatie downloaden") }}. <a href="https://www.test-correct.nl/downloads/">{{ __("student.Klik hier") }}</a> {{ __("student.om dat te doen") }};</li>
                <li>{{ __("student.Zorg ervoor dat je ten minste één keer in de Test-Correct applicatie bent ingelogd voordat je een toets moet maken") }};</li>
                <li>{{ __("student.Zorg ervoor dat je genoeg tijd hebt om de applicatie voor je toets te downloaden en te starten. Het kan zijn dat je de instellingen van je computer moet aanpassen, dus geef jezelf voldoende tijd") }};</li>
                <li>{{ __("student.Je kan bij het installeren meldingen van Test-Correct op je scherm krijgen waarvoor je actie moet ondernemen. Lees in de") }} <a href="{{ config('app.knowledge_bank_url') }}/studenthandleiding">{{ __("student.handleiding") }}</a> {{ __("student.wat je moet doen (zie onder)") }};</li>
                <li>{{ __("student.De applicatie is beveiligd en dat houdt o.a. in dat er geen andere vensters open mogen staan tijdens het maken van een toets") }};</li>
                <li><a href="{{ config('app.knowledge_bank_url') }}/studenthandleiding"><strong>{{ __("student.Klik hier om de handleiding te downloaden") }}.</strong></a> {{ __("student.Daar staat alles wat je moet doen om je toets te kunnen maken: applicatie downloaden, applicatie starten, inloggen, toets maken, en CO-Learning") }};</li>
                <li>{{ __("student.Heb je hulp nodig en staat er in de handleiding niet wat je moet doen? Neem contact met ons op door een email te sturen naar support@test-correct.nl. Beschrijf in je email goed wat het probleem is zodat we jou goed kunnen helpen") }}.</li>
            </ul>
         <br/>
            {{__('student.E-mail')}}: {{ $user->username }}<br/>
            {{__('student.Je kunt je wachtwoord instellen op')}}:<br />
            <a href="{{ $url }}">{{ $url }}</a><br/>
            <br/>
            {{__('student.Deze link verloopt vier uur nadat dit e-mailbericht werd verstuurd. Je kunt een nieuwe link laten opsturen via de wachtwoord vergeten functionaliteit.')}}
            <BR/> <a href="{{ \tcCore\Http\Helpers\BaseHelper::getLoginUrl() }}">{{__('student.Nieuwe verzoek opsturen')}}</a><br/>
            <br/>
            <br/>
            {{ __("student.Het team van Test-Correct wenst jou heel veel succes met je toetsen") }}! 😊<br/>
        </td>
    </tr>
@stop
