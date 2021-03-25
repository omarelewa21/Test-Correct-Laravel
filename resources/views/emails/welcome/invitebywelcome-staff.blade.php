@extends('emails.layout')

@section('content')
    @php
        $name = $user->name_first;
        if(strlen($name) == 1
            || (strlen($name) == 2 && $name{1} === '.')){
                $name = sprintf('%s %s %s',$name,$user->name_suffix,$user->name);
        }

        $colleague = str_replace('  ',' ',(sprintf("%s %s %s", $user->invitedby->name_first, $user->invitedby->name_suffix, $user->invitedby->name)));


        \Bugsnag\BugsnagLaravel\Facades\Bugsnag::leaveBreadcrumb('invitedbywelcome-staff.blade.php');
    @endphp
    <tr>
        <td colspan="999" style="padding:20px;">
            Hallo {{$name}}!<br/>
            <br/>
            Je collega {{$colleague}} nodigt je uit om Test-Correct uit te proberen, een platform voor digitaal toetsen.
            We hebben direct een account voor je aangemaakt voor Test-Correct. Je bent daarmee een stap dichterbij een onderwijsinnovatie die echt eenvoudig is om te gebruiken en toch een wezenlijk verschil maakt!<br/>
            <br/>
            Het ontdekken hoe Test-Correct werkt kan enkele minuten duren, of misschien een uur, maar wanneer je het eenmaal doorhebt is het klik-klak-klaar.<br/>
            <br/>
            Mijn naam is Alex en ik wil je graag helpen om alles zo soepel mogelijk te laten verlopen: een soort mentor :-)<br/>
            <br/>
            Ik raad je aan om direct te beginnen met onze demo tour. Ik leid je daar op een leuke en interactieve manier door de belangrijkste stappen van Test-Correct.<br/>
            <br/>

            E-mail: {{ $user->username }}<br/>
            <br/>
            U kunt uw wachtwoord instellen op:<br />
            <a href="{{ $url }}">{{ $url }}</a><br/>
            <br/>
            Deze link verloopt vier uur nadat dit e-mailbericht werd verstuurd. U kunt een nieuwe link laten opsturen via de wachtwoord vergeten functionaliteit.
            <br /> <a href="{{ config('app.url_login') }}">Nieuwe verzoek opsturen</a><br/>
            <br/>
            <a href="{{ config('app.url_login')}}" style="background-color: #42b947;padding: 15px 30px;margin-bottom: 30px;display: inline-block;color:#ffffff;text-decoration:none">Login en start demo</a><br/>
            <br/>
            Met vriendelijke groet,<br/>
            <br/>
            Alex<br />
            Test-Correct Mentor
        </td>
    </tr>
@stop
