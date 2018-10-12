<tr>
<td colspan="999" style="padding:20px;">
Beste {{ $receiver->nameFull }},<br/>
<br/>
{{ $sentMessage->user->nameFull }} heeft je via Test-correct het volgende bericht gestuurd:<br/>
<br/>
{!! nl2br(e($sentMessage->message)) !!}
</td>
</tr>
