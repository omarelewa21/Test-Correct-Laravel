@extends('emails.layout')

@section('content')
<tr>
<td colspan="999" style="padding:20px;">
Beste {{ $receiver->getNameFullAttribute() }},<br/>
<br/>
{{ $sentMessage->user->getNameFullAttribute() }} heeft via Test-Correct het volgende bericht naar je gestuurd:<br/>
<br/>
{!! nl2br(e($sentMessage->message)) !!}
<br/>
Via <a href="{{$urlLogin}}">Test-Correct</a> kan je hierop reageren.<br/>
<br/>
Met een vriendelijke groet,<br/>
<br/>
Test-Correct supportteam
</td>
</tr>
@stop