@extends('emails.layout')

@section('content')
<tr>
<td colspan="999" style="padding:20px;">
    {{ __("assignment_planned.Beste") }} {{ $testParticipant->user->getNameFullAttribute() }},<br/>
<br/>
{{ __("assignment_planned.Jouw docent heeft zojuist een opdracht ingepland") }}. {{ __("assignment_planned.Op") }} {{ DateTime::createFromFormat('Y-m-d H:i:s', $testParticipant->testTake->time_start)->format('d-m-Y') }}  {{__('assignment_planned.wordt de opdracht')}}  "{{ $testParticipant->testTake->test->name }}" {{__('assignment_planned.van het vak')}} {{ $testParticipant->testTake->test->subject->name }} {{ __("assignment_planned.afgenomen") }}.<br/>

<br/>
{{ __("test_planned.assignment_text_access_link") }}
<a href="{{$directlink}}">
    <strong>{{__("test_planned.this_link")}}</strong>
</a>
@if ($takeCode)
    {{__('test_planned.assignment_take_code')}}: <strong>{{$takeCode}}</strong>
@endif
<br/>
<br/>

{{ __("assignment_planned.Bereid je goed voor op deze opdracht. Succes") }}!<br/>
<br/>
{{ __("assignment_planned.Test-Correct supportteam") }}
</td>
</tr>
@stop