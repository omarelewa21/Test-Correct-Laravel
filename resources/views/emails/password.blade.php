@extends('emails.layout')

@section('content')
<tr>
<td colspan="999" style="padding:20px;">
Beste {{$user->getNameFullAttribute()}},<br/>
<br/>
U hebt onlangs verzocht om uw wachtwoord voor uw Test-Correct account opnieuw in te stellen. Klik op de link hieronder om dit proces af te ronden.<br/>
<br/>
<a href="{{ sprintf($url, $token) }}">{{ sprintf($url, $token) }}</a><br/>
<br/>
Deze link verloopt<!--  NIET --> één uur nadat dit e-mailbericht werd verstuurd.<br/>
<br/>
Als u dit verzoek niet zelf hebt gedaan, is het zeer waarschijnlijk dat een andere gebruiker uw e-mailadres per ongeluk heeft gebruikt en dat uw account nog gewoon veilig is. Als u denkt dat een ongeautoriseerd persoon toegang heeft gehad tot uw account, kunt u uw wachtwoord opnieuw instellen op <a href="{{$urlLogin}}">het loginscherm</a> op de knop ‘Wachtwoord vergeten’ te klikken.<br/>
<br/>
Met vriendelijke groet,<br/>
Test-Correct supportteam
</td>
</tr>
@stop
