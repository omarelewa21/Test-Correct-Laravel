@extends('emails.layout')

@section('content')
<tr>
<td colspan="999" style="padding:20px;">
Beste {{$user->getNameFullAttribute()}},<br/>
<br/>
Welkom bij Test-Correct!<br/>
<br/>
    Wat een bijzondere tijd! Toch moet niets in de weg van het onderwijs staan. Wij hopen dat ons toetsingsplatform hieraan een steentje bij kan dragen.<br/>
    <br />
    Met Test-Correct heeft u de mogelijkheid om uw leerlingen op afstand te toetsen en overzicht te houden over hun voortgang. Ook kunt u de afgenomen toetsen op afstand bespreken. Gebruik de onderstaande inloggegevens om in te loggen op <a href="https://portal.test-correct.nl">https://portal.test-correct.nl</a><br />
    <br />
Uw gebruikersnaam: {{ $user->username }}<br/>
Uw wachtwoord: {{ $password }}<br/>
<br/>
    Om de beste dienst aan u te kunnen leveren, vragen wij u om de onderstaande acties te nemen.<br/>
    <br />
    <b>Klasgegevens:</b> Sturen naar support@test-correct.nl, met het titel: <i>Lesgroepen, <schoolnaam></i>.<br />
    Voor iedere lesgroep hebben wij de volgende informatie nodig:
    <ul>
        <li>Naam lesgroep</li>
        <li>Niveau</li>
        <li>Leerjaar</li>
        <li>Welke docenten geven les aan deze lesgroep en voor welk vak</li>
        <li>Leerlingenlijst met daarin:
            <ul>
                <li>Volledige naam•Leerlingnummer</li>
                <li>E-mailadres</li>
                <li>Optioneel: tijdsdispensatie ja/ nee</li>
                <li>Optioneel: recht om tekst naar spraak om te zetten ja/nee (er zijn kosten verbonden)</li>
                <li>Let op: aangemelde leerlingen krijgen van ons een welkomstmail met daarin login-gegevens.</li>
            </ul>
        </li>
    </ul>
    <b>Collega uitnodigen:</b> Kent u een collega die met Test-Correct zou willen werken? U kunt de voor- en achternaam met het emailadres naar info@test-correct.nl sturen met het onderwerp “Collega”, dan sturen wij vrijblijvend de nodige informatie.<br/>
    <b>Training:</b> Meld u aan voor een 45-minuten durende webinar, dit doet u <a href="https://www.academy4learning.nl/aanmeldformulier-webinar-lesgeven-op-afstand/">hier</a>.<br />
    <b>Instructies:</b> Raadpleeg onze <a href="https://www.test-correct.nl/support/">supportpagina</a> voor instructies over het gebruik van Test-Correct, of download de volledige handleiding <a href="http://www.test-correct.nl/downloadables/Docenthandleiding_v1.1.0_rev.1.0.pdf">hier</a>.<br />
    <b>Toetsen uploaden:</b> Upload <a href="https://www.test-correct.nl/toets-uploaden/">hier</a> de toetsen die u wilt afnemen (in een PDF, Word of QTI).<br />
    <b>Studenten:</b>
    <ul>
        <li><b>App installeren:</b> Laat de studenten de applicatie installeren op hun device om toetsen te kunnen maken, dat kan <a href="https://www.test-correct.nl/downloads/">hier</a>.<br/>
            <b>Let op:</b> Zonder de app kunnen de studenten de toets niet maken.</li>
        <li><b>Instructies:</b> Verwijs uw leerlingen naar de <a href="https://www.test-correct.nl/support/student/">studenten supportpagina</a>, of stuur de studentenhandleiding in een <a href="https://www.test-correct.nl/wp-content/uploads/2020/04/Leerlingenhandleiding-Algemeen.pdf">PDF</a> door.</li>
    </ul>
<br/>
    Als u vragen hebt, kunt u telefonisch contact met ons opnemen via het telefoonnummer 010 7 171 171 of stuur een email naar support@test-correct.nl.<br/>
<br/>
Veel plezier met Test-Correct!<br/>
<br/>
Test-Correct supportteam
</td>
</tr>
@stop