@extends('emails.layout')

@section('content')
<tr>
<td colspan="999" style="padding:20px;">
    {{ __("staff.Beste") }} {{ $receiver->getNameFullAttribute() }},<br/>
<br/>
{{ $sentMessage->user->getNameFullAttribute() }} {{ __("staff.heeft u via Test-Correct het volgende bericht gestuurd") }}:<br/>
<br/>
{!! nl2br(e($sentMessage->message)) !!}
<br/>
{{ __("staff.Als u hierop wilt reageren dient u dit via") }} <a href="{{$urlLogin}}">Test-Correct</a> {{ __("staff.te doen.") }}<br/>
<br/>
{{ __("staff.Met een vriendelijke groet") }},<br/>
<br/>
{{ __("staff.Test-Correct supportteam") }}
</td>
</tr>
@stop
