@extends('emails.layout')

@section('content')
    @php
        $name = $user->name_first;
        if(strlen($name) == 1
            || (strlen($name) == 2 && $name{1} === '.')){
                $name = sprintf('%s %s %s',$name,$user->name_suffix,$user->name);
        }
    @endphp
    <tr>
        <td colspan="999" style="padding:20px;">
            Hallo {{$name}}!<br/>
            <br/>
            Supergoed dat je een account hebt aangemaakt voor Test-Correct. Je bent een stap dichterbij een
            onderwijsinnovatie die echt eenvoudig is om te gebruiken en toch een wezenlijk verschil maakt!<br/>
            <br/>
            Het ontdekken hoe Test-Correct werkt kan enkele minuten duren, of misschien een paar uur, maar wanneer je
            het eenmaal doorhebt is het klik-klak-klaar.<br/>
            <br/>
            Mijn naam is Alex en ik ga je helpen om alles zo soepel mogelijk te laten verlopen: een soort mentor :-)<br/>
            <br/>
            Ik raad je aan om direct te beginnen met onze demo tour. Ik leid je daar op een leuke en interactieve manier
            door de belangrijkste stappen van Test-Correct.<br/>
            <br/>

            E-mail: {{ $user->username }}<br/>

            Je kunt je wachtwoord instellen op:<br />
            <a href="{{ $url }}">{{ $url }}</a><br/>
            <br/>
            Deze link verloopt vier uur nadat dit e-mailbericht werd verstuurd. Je kunt een nieuwe link laten opsturen via de wachtwoord vergeten functionaliteit.
            <BR/> <a href="{{ config('app.url_login') }}">Nieuwe verzoek opsturen</a><br/>
            <br/>

            Met vriendelijke groet,<br/>
            <br/>
            Alex<br />
            Test-Correct Mentor
        </td>
    </tr>
@stop
