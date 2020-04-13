@extends('emails.layout')

@section('content')
<tr>
<td colspan="999" style="padding:20px;">
Beste {{ $receiver->getNameFullAttribute() }},<br/>
<br/>
{{ $sentMessage->user->getNameFullAttribute() }} heeft u via Test-Correct het volgende bericht gestuurd:<br/>
<br/>
{!! nl2br(e($sentMessage->message)) !!}
<br/>
Als u hierop wilt reageren dient u dit via <a href="{{$urlLogin}}">Test-Correct</a> te doen.<br/>
<br/>
Met een vriendelijke groet,<br/>
<br/>
Test-Correct supportteam
</td>
</tr>
@stop
