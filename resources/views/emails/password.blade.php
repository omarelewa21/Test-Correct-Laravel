@extends('emails.layout')

@section('content')
<tr>
<td colspan="999" style="padding:20px;">
    {{ __("password.Beste") }} {{$user->getNameFullAttribute()}},<br/>
<br/>
{{ __("password.Je hebt onlangs verzocht om je wachtwoord voor je Test-Correct account opnieuw in te stellen. Klik op de link hieronder om dit proces af te ronden.") }}<br/>
<br/>
<a href="{!! sprintf($url, $token) !!}">{!! sprintf($url, $token) !!}</a><br/> {{-- todo: vertaling toevoegen:  Wachtwoord opnieuw instellen --}}
<br/>
{{ __("password.Deze link verloopt vier uur nadat dit e-mailbericht werd verstuurd.") }}<br/>
<br/>
{{ __("password.Als je dit verzoek om het wachtwoord opnieuw in te stellen niet zelf hebt gedaan, is het zeer waarschijnlijk dat een andere gebruiker je e-mailadres per ongeluk heeft gebruikt en dat je account nog gewoon veilig is. Als je denkt dat een ongeautoriseerd persoon toegang heeft gehad tot je account, kun je het wachtwoord opnieuw instellen door op het loginscherm op de knop ‘Wachtwoord vergeten’ te klikken") }}.<br/>
<br/> {{-- op de knop ‘Wachtwoord vergeten’ te klikken. vervangen met: 'opnieuw instellen' (hoe de link heet) --}}
    <a href="{{ \tcCore\Http\Helpers\BaseHelper::getLoginUrl()}}" style="background-color: #42b947;padding: 15px 30px;margin-bottom: 30px;display: inline-block;color:#ffffff;text-decoration:none"> {{__('password.Loginscherm')}} </a><br/>
    <br/>
    {{ __("password.Met vriendelijke groet") }},<br/>
    {{ __("password.Test-Correct supportteam") }}
</td>
</tr>
@stop
