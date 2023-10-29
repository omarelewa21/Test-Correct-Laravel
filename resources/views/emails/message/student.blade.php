@extends('emails.layout')

@section('content')
<tr>
<td colspan="999" style="padding:20px;">
    {{ __("student.Beste") }} {{ $receiver_name }},<br/>
<br/>
{{ $sentMessage->user->getNameFullAttribute() }} {{ __("student.heeft via Test-Correct het volgende bericht naar je gestuurd") }}:<br/>
<br/>
{!! nl2br(e($sentMessage->message)) !!}
<br/>
{{ __("student.Via") }} <a href="{{$urlLogin}}">Test-Correct</a> {{ __("student.kan je hierop reageren") }}.<br/>
<br/>
{{ __("student.Met een vriendelijke groet") }},<br/>
<br/>
{{ __("student.Test-Correct supportteam") }}
</td>
</tr>
@stop