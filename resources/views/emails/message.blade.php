@extends('emails.layout')

@section('content')
<tr>
<td colspan="999" style="padding:20px;">
    {{ __("message.Beste") }} {{ $receiver->nameFull }},<br/>
<br/>
{{ $sentMessage->user->nameFull }} {{ __("message.heeft je via Test-correct het volgende bericht gestuurd") }}:<br/>
<br/>
{!! nl2br(e($sentMessage->message)) !!}
</td>
</tr>
@stop
