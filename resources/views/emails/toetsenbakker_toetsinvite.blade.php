@extends('emails.layout')

@section('content')
<tr>
<td colspan="999" style="padding:20px;">
    {{ __("toetsenbakker_toetsinvite.Beste toetsenbakker") }},<br/>
<br/>
{{ __("toetsenbakker_toetsinvite.Er staat weer een toets voor je klaar om in te voeren") }}:
    <table style="width:100%;">
        <thead>
            <tr>
                <td><b>{{ __("toetsenbakker_toetsinvite.Aanleverdatum") }}</b></th>
                <td><b>{{ __("toetsenbakker_toetsinvite.Docent") }}</b></th>
                <td><b>{{ __("toetsenbakker_toetsinvite.Vak") }}</b></th>
                <td><b>{{ __("toetsenbakker_toetsinvite.Naam") }}</b></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $fileManagement->created_at->format('d M \'y \o\m H:i \u\u\r') }}</td>
                <td>{{ $fileManagement->user->getNameFullAttribute() }}</td>
                <td>{{ $fileManagement->typedetails->subject }}</td>
                <td>{{ $fileManagement->typedetails->name }}</td>
            </tr>
        </tbody>
    </table>
<br/>
{{ __("toetsenbakker_toetsinvite.Je kunt inloggen met het account") }}: <b>{{ $fileManagement->handler->username }}</b><br />
<br />
{{ __("toetsenbakker_toetsinvite.Voordat je start met het bakken van de toets, verander eerst de status naar ‘in behandeling', ben je klaar verander de status naar ‘klaar voor eerste controle’. Wil je pauzeren en op een later moment verder met deze toets, verander de status in ‘behandeling gepauzeerd’") }}.<br />
<br/>
{{ __("toetsenbakker_toetsinvite.Met een vriendelijke groet") }},<br/>
<br/>
{{ __("toetsenbakker_toetsinvite.Test-Correct supportteam") }}
</td>
</tr>
@stop