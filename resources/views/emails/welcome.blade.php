@extends('emails.layout')

@section('content')
<tr>
<td colspan="999" style="padding:20px;">
Welkom bij Test-correct, {{ $user->nameFull }}.<br/>
<br/>
Je gebruikersnaam is {{ $user->username }}<br/>
    Je kunt je wachtwoord instellen op:<br />
    <a href="{{ sprintf($url, $token) }}">{{ sprintf($url, $token) }}</a><br/>
    <br/>
    Deze link verloopt vier uur nadat dit e-mailbericht werd verstuurd. Je kunt een nieuwe link laten opsturen via de wachtwoord vergeten functionaliteit.
    <BR/> <a href="{{ config('app.url_login') }}">Nieuwe verzoek opsturen</a><br/>
    <br/>
</td>
</tr>
@stop
