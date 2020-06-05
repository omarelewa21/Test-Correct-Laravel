@extends('emails.layout')

@section('content')
<tr>
<td colspan="999" style="padding:20px;">
Beste {{$user->getNameFullAttribute()}},<br/>
<br/>
    Je hebt onlangs verzocht om je wachtwoord voor je Test-Correct account opnieuw in te stellen. Klik op de link hieronder om dit proces af te ronden.<br/>
<br/>
<a href="{{ sprintf($url, $token) }}">{{ sprintf($url, $token) }}</a><br/>
<br/>
Deze link verloopt vier uur nadat dit e-mailbericht werd verstuurd.<br/>
<br/>
    Als je dit verzoek om het wachtwoord opnieuw in te stellen niet zelf hebt gedaan, is het zeer waarschijnlijk dat een andere gebruiker je e-mailadres per ongeluk heeft gebruikt en dat je account nog gewoon veilig is. Als je denkt dat een ongeautoriseerd persoon toegang heeft gehad tot je account, kun je het wachtwoord opnieuw instellen door op het loginscherm op de knop ‘Wachtwoord vergeten’ te klikken.<br/>
<br/>
    <a href="{{ config('app.url_login')}}" style="background-color: #42b947;padding: 15px 30px;margin-bottom: 30px;display: inline-block;color:#ffffff;text-decoration:none">Loginscherm</a><br/>
    <br/>
Met vriendelijke groet,<br/>
Test-Correct supportteam
</td>
</tr>
@stop
