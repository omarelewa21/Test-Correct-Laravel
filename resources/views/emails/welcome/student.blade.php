@extends('emails.layout')

@section('content')
<tr>
<td colspan="999" style="padding:20px;">
Beste {{$user->getNameFullAttribute()}},<br/>
<br/>
Welkom bij Test-Correct!<br/>
<br/>
Leuk dat je gaat werken in Test-Correct. Niets staat je nog in de weg om betere resultaten te behalen. Je actieve bijdrage bij het bespreken van de toets gaat ervoor zorgen dat jij veel meer gaat begrijpen en onthouden, de automatische analyse wijst je ook nog eens op je sterktes en zwaktes! Een goede voorbereiding voor ieder examen. Niets staat een prachtig diploma nog in de weg!<br/>
Je gebruikersnaam: {{ $user->username }}<br/>
Je wachtwoord: {{ $password }}<br/>
<br/>
Lees deze tips voordat je aan de slag gaat:<br/>
<ul>
<li><a href="http://www.test-correct.nl/support/student/toets-maken/">Toets maken</a><br/>Bekijk wat je kan verwachten bij het maken van een toets in Test-Correct.</li>
<li><a href="http://www.test-correct.nl/support/student/toets-bespreken/">Toets bespreken</a><br/>Hier zit het geheim van jouw succes. Bekijk waarom.</li>
<li><a href="http://www.test-correct.nl/support/student/analyseren/">Analyseren</a><br/>Wat zeggen de analyses over jouw?</li>
</ul>
<br/>
Veel plezier met Test-Correct & succes met de toetsen!<br/>
<br/>
Test-Correct supportteam
</td>
</tr>
@stop