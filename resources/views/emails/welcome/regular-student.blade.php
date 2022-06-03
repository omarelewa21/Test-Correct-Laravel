@extends('emails.layout')
@php
 \Bugsnag\BugsnagLaravel\Facades\Bugsnag::leaveBreadcrumb('regular-student.blade.php');
@endphp
@section('content')
<tr>
<td colspan="999" style="padding:20px;">
    {{ __("regular-student.Beste") }} {{$user->getNameFullAttribute()}},<br/>
<br/>
{{ __("regular-student.Welkom bij Test-Correct") }}!<br/>
<br/>
{{__('regular-student.Leuk dat je gaat werken in Test-Correct. Niets staat je nog in de weg om betere resultaten te behalen. Je actieve bijdrage bij het bespreken van de toets gaat ervoor zorgen dat jij veel meer gaat begrijpen en onthouden, de automatische analyse wijst je ook nog eens op je sterktes en zwaktes! Een goede voorbereiding voor ieder examen. Niets staat een prachtig diploma nog in de weg!')}}<br/>
{{__('regular-student.Je gebruikersnaam:')}} {{ $user->username }}<br/>
    {{__('regular-student.Je kunt je wachtwoord instellen op:')}}<br />
    <a href="{{ sprintf($url, $token) }}">{{ sprintf($url, $token) }}</a><br/>
    <br/>
    {{__('regular-student.Deze link verloopt vier uur nadat dit e-mailbericht werd verstuurd. Je kunt een nieuwe link laten opsturen via de wachtwoord vergeten functionaliteit.')}}
    <BR/> <a href="{{ \tcCore\Http\Helpers\BaseHelper::getLoginUrl() }}">{{__('regular-student.Nieuwe verzoek opsturen')}}</a><br/>
    <br/>
<br/>
{{ __("regular-student.Lees deze tips voordat je aan de slag gaat") }}:<br/>
<ul>
<li><a href="http://www.test-correct.nl/support/student/toets-maken/">{{ __("regular-student.Toets maken") }}</a><br/>{{ __("regular-student.Bekijk wat je kan verwachten bij het maken van een toets in Test-Correct") }}.</li>
<li><a href="http://www.test-correct.nl/support/student/toets-bespreken/">{{ __("regular-student.CO-Learning") }}</a><br/>{{ __("regular-student.Hier zit het geheim van jouw succes. Bekijk waarom") }}.</li>
<li><a href="http://www.test-correct.nl/support/student/analyseren/">{{ __("regular-student.Analyseren") }}</a><br/>{{ __("regular-student.Wat zeggen de analyses over jouw") }}?</li>
</ul>
<br/>
{{ __("regular-student.Veel plezier met Test-Correct & succes met de toetsen") }}!<br/>
<br/>
{{ __("regular-student.Test-Correct supportteam") }}
</td>
</tr>
@stop
