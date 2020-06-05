@extends('emails.layout')

@section('content')
    <tr>
        <td colspan="999" style="padding:20px;">
            Beste {{$user->getNameFullAttribute()}},<br/>
            <br/>
            Welkom bij Test-Correct!<br/>
            <br/>
            Je docent heeft je opgegeven voor Test-Correct. Je docent gaat Test-Correct gebruiken om toetsen digitaal af te nemen.<br />
            <br/>
            Je actieve bijdrage bij het bespreken van de toets gaat ervoor zorgen dat jij veel meer gaat begrijpen en onthouden en de automatische analyse wijst je ook nog eens op je sterktes en zwaktes! Een goede voorbereiding voor ieder examen. Niets staat een prachtig diploma nog in de weg!<br/>
            <br/>
            Om Test-Correct te kunnen gebruiken heb je een app nodig. Installeer deze tijdig, voordat je een toets hebt. Deze kun je downloaden via onze website <a href="https://www.test-correct.nl">www.test-correct.nl</a>. Klik hiervoor op het menu icoon rechts bovenaan de startpagina en kies “Downloads”. Je kunt ook onderstaande link gebruiken.<br/>
            <br/>
            <a href="https://www.test-correct.nl/downloads/" style="background-color: #42b947;padding: 15px 30px;margin-bottom: 30px;display: inline-block;color:#ffffff;text-decoration:none">App downloaden</a><br/>
            <br/>
            Als je de app opent, heb je onderstaande gegevens nodig om in te loggen:<br/>
            E-mail: {{ $user->username }}<br/>
            Wachtwoord: {{ $password }}<br/>
            <br/>
            Veel plezier met Test-Correct en succes met de toetsen!<br/>
            <br/>
            Test-Correct supportteam
        </td>
    </tr>
@stop