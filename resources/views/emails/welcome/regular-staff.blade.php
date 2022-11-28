@extends('emails.layout')
@php
    \Bugsnag\BugsnagLaravel\Facades\Bugsnag::leaveBreadcrumb('regular-staff.blade.php');
@endphp
@section('content')
<tr>
<td colspan="999" style="padding:20px;">
    {{ __("regular-staff.Beste") }} {{$user->getNameFullAttribute()}},<br/>
<br/>
{{ __("regular-staff.Welkom bij Test-Correct") }}!<br/>
<br/>
{{__('regular-staff.Leuk dat u gaat werken in Test-Correct. Niets staat u nog in de weg om bestaande & nieuwe toetsen te creëren om vervolgens af te nemen, te bespreken & te analyseren. U bereikt al snel 80% reductie van het nakijkwerk en een aanzienlijk verhoogd leerrendement, terwijl u nog steeds gebruik kunt maken van open vragen!')}}<br/>
{{__('regular-staff.Uw gebruikersnaam:')}} {{ $user->username }}<br/>
    {{__('regular-staff.U kunt uw wachtwoord instellen op:')}}<br />
    <a href="{{ sprintf($url, $token) }}">{{ sprintf($url, $token) }}</a><br/>
    <br/>
    {{__('regular-staff.Deze link verloopt vier uur nadat dit e-mailbericht werd verstuurd. U kunt een nieuwe link laten opsturen via de wachtwoord vergeten functionaliteit.')}}
    <br /> <a href="{{ \tcCore\Http\Helpers\BaseHelper::getLoginUrl() }}">{{__('regular-staff.Nieuwe verzoek opsturen')}}</a><br/>
<br/>
{{ __("regular-staff.Lees deze tips voordat u aan de slag gaat") }}:<br/>
<ul>
<li><a href="http://www.test-correct.nl/support/toets-creeren/">{{ __("regular-staff.Toets creëren") }}</a><br/>{{ __("regular-staff.Welke vraagsoorten kan je maken en hoe maak je daar optimaal gebruik van. Waarop let als je een bestaande toets importeert") }}. </li>
<li><a href="http://www.test-correct.nl/support/toets-afnemen/">{{ __("regular-staff.Toets afnemen") }}</a><br/>{{ __("regular-staff.Het inplannen & surveilleren van de toets. Tips & Trucs bij het surveilleren") }}.</li>
<li><a href="http://www.test-correct.nl/support/toets-bespreken/">{{ __("regular-staff.CO-Learning") }}</a><br/>{{ __("regular-staff.De kracht van Test-Correct. Onderwijskundige Tips & Trucs bij het bespreken") }}.</li>
<li><a href="http://www.test-correct.nl/support/toets-nakijken-en-analyseren/">{{ __("regular-staff.Toets nakijken en analyseren") }}</a><br/>{{ __("regular-staff.Je eigen manier van nakijken. De interessantste analyses") }}.</li>
</ul>
<br/>
{{ __("regular-staff.Veel plezier met Test-Correct") }}!<br/>
<br/>
{{ __("regular-staff.Test-Correct supportteam") }}
</td>
</tr>
@stop
