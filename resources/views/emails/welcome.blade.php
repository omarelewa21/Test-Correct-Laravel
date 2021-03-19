@extends('emails.layout')

@section('content')
<tr>
<td colspan="999" style="padding:20px;">
    {{ __("welcome.Welkom bij Test-correct") }}, {{ $user->nameFull }}.<br/>
<br/>
{{ __("welcome.Je gebruikersnaam is") }} {{ $user->username }}<br/>
{{ __("welcome.Je wachtwoord is") }} {{ $password }}<br/>
</td>
</tr>
@stop