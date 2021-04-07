@extends('emails.layout')
@php
    \Bugsnag\BugsnagLaravel\Facades\Bugsnag::leaveBreadcrumb('regular-staff.blade.php');
@endphp
@section('content')
<tr>
<td colspan="999" style="padding:20px;">
Beste {{$user->getNameFullAttribute()}},<br/>
<br/>
Welkom bij Test-Correct!<br/>
<br/>
Leuk dat u gaat werken in Test-Correct. Niets staat u nog in de weg om bestaande & nieuwe toetsen te creëren om vervolgens af te nemen, te bespreken & te analyseren. U bereikt al snel 80% reductie van het nakijkwerk en een aanzienlijk verhoogd leerrendement, terwijl u nog steeds gebruik kunt maken van open vragen!<br/>
Uw gebruikersnaam: {{ $user->username }}<br/>
    U kunt uw wachtwoord instellen op:<br />
    <a href="{{ sprintf($url, $token) }}">{{ sprintf($url, $token) }}</a><br/>
    <br/>
    Deze link verloopt vier uur nadat dit e-mailbericht werd verstuurd. U kunt een nieuwe link laten opsturen via de wachtwoord vergeten functionaliteit.
    <br /> <a href="{{ config('app.url_login') }}">Nieuwe verzoek opsturen</a><br/>
<br/>
Lees deze tips voordat u aan de slag gaat:<br/>
<ul>
<li><a href="http://www.test-correct.nl/support/toets-creeren/">Toets creëren</a><br/>Welke vraagsoorten kan je maken en hoe maak je daar optimaal gebruik van. Waarop let als je een bestaande toets importeert. </li>
<li><a href="http://www.test-correct.nl/support/toets-afnemen/">Toets afnemen</a><br/>Het inplannen & surveilleren van de toets. Tips & Trucs bij het surveilleren.</li>
<li><a href="http://www.test-correct.nl/support/toets-bespreken/">Toets bespreken</a><br/>De kracht van Test-Correct. Onderwijskundige Tips & Trucs bij het bespreken.</li>
<li><a href="http://www.test-correct.nl/support/toets-nakijken-en-analyseren/">Toets nakijken en analyseren</a><br/>Je eigen manier van nakijken. De interessantste analyses.</li>
</ul>
<br/>
Veel plezier met Test-Correct!<br/>
<br/>
Test-Correct supportteam
</td>
</tr>
@stop
