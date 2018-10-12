@extends('emails.layout')

@section('content')
<tr>
<td colspan="999" style="padding:20px;">
Beste {{ $testParticipant->user->getNameFullAttribute() }},<br/>
<br/>
Jouw docent heeft zojuist een toetsafname gepland. Op {{ DateTime::createFromFormat('Y-m-d H:i:s', $testParticipant->testTake->time_start)->format('d-m-Y') }} wordt de toets "{{ $testParticipant->testTake->test->name }}" van het vak {{ $testParticipant->testTake->test->subject->name }} afgenomen.<br/>
<br/>
Bereid je goed voor op deze toets. Succes!<br/>
<br/>
Test-Correct supportteam
</td>
</tr>
@stop