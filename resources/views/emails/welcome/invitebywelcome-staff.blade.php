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
            {{ __("invitebywelcome-staff.Hallo") }} {{$name}}!<br/>
            <br/>
            {{ __("invitebywelcome-staff.Je collega") }} {{$colleague}} {{ __("invitebywelcome-staff.nodigt je uit om Test-Correct uit te proberen, een platform voor digitaal toetsen.") }}
            {{ __("invitebywelcome-staff.We hebben direct een account voor je aangemaakt voor Test-Correct.") }} {{ __("invitebywelcome-staff.Je bent daarmee een stap dichterbij een onderwijsinnovatie die echt eenvoudig is om te gebruiken en toch een wezenlijk verschil maakt!") }}<br/>
            <br/>
            {{ __("invitebywelcome-staff.Het ontdekken hoe Test-Correct werkt kan enkele minuten duren, of misschien een uur, maar wanneer je het eenmaal doorhebt is het klik-klak-klaar.") }}<br/>
            <br/>
            {{ __("invitebywelcome-staff.Mijn naam is Alex en ik wil je graag helpen om alles zo soepel mogelijk te laten verlopen: een soort mentor") }} :-)<br/>
            <br/>
            {{ __("invitebywelcome-staff.Ik raad je aan om direct te beginnen met onze demo tour. Ik leid je daar op een leuke en interactieve manier door de belangrijkste stappen van Test-Correct") }}.<br/>
            <br/>

            {{__("invitebywelcome-staff.E-mail")}}: {{ $user->username }}<br/>
            <br/>
            {{__("invitebywelcome-staff.U kunt uw wachtwoord instellen op")}}:<br />
            <a href="{{ $url }}">{{ $url }}</a><br/>
            <br/>
            {{__("invitebywelcome-staff.Deze link verloopt vier uur nadat dit e-mailbericht werd verstuurd. U kunt een nieuwe link laten opsturen via de wachtwoord vergeten functionaliteit.")}}
            <br/>
            <a href="{{ \tcCore\Http\Helpers\BaseHelper::getLoginUrl()}}" style="background-color: #42b947;padding: 15px 30px;margin-bottom: 30px;display: inline-block;color:#ffffff;text-decoration:none">{{__('invitebywelcome-staff.Login en start demo')}}</a><br/>
            <br/>
            {{ __("invitebywelcome-staff.invitebywelcome-staff.Met vriendelijke groet") }},<br/>
            <br/>
            Alex<br />
            Test-Correct Mentor
        </td>
    </tr>
@stop
