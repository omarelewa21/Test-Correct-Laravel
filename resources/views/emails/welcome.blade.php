@extends('emails.layout')

@section('content')
<tr>
<td colspan="999" style="padding:20px;">
Welkom bij Test-correct, {{ $user->nameFull }}.<br/>
<br/>
Je gebruikersnaam is {{ $user->username }}<br/>
Je wachtwoord is {{ $password }}<br/>
</td>
</tr>
@stop