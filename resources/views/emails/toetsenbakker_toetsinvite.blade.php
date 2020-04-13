@extends('emails.layout')

@section('content')
<tr>
<td colspan="999" style="padding:20px;">
Beste toetsenbakker,<br/>
<br/>
Er staat weer een toets voor je klaar om in te voeren:
    <table style="width:100%;">
        <thead>
            <tr>
                <td><b>Aanleverdatum</b></th>
                <td><b>Docent</b></th>
                <td><b>Vak</b></th>
                <td><b>Naam</b></th>
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
    Je kunt inloggen met het account: <b>{{ $fileManagement->handler->username }}</b><br />
<br />
Voordat je start met het bakken van de toets, verander eerst de status naar ‘in behandeling', ben je klaar verander de status naar ‘klaar voor eerste controle’. Wil je pauzeren en op een later moment verder met deze toets, verander de status in ‘behandeling gepauzeerd’.<br />
<br/>
Met een vriendelijke groet,<br/>
<br/>
Test-Correct supportteam
</td>
</tr>
@stop