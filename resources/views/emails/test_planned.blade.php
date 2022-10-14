@extends('emails.layout')

@section('content')
<tr>
<td colspan="999" style="padding:20px;">
    {{ __("test_planned.Beste") }} {{ $testParticipant->user->getNameFullAttribute() }},<br/>
<br/>
{{ __("test_planned.Jouw docent heeft zojuist een toetsafname gepland") }}. {{ __("test_planned.Op") }} {{ DateTime::createFromFormat('Y-m-d H:i:s', $testParticipant->testTake->time_start)->format('d-m-Y') }}  {{__('test_planned.wordt de toets')}}  "{{ $testParticipant->testTake->test->name }}" {{ __('test_planned.van het vak') }} {{ $testParticipant->testTake->test->subject->name }} {{ __("test_planned.afgenomen") }}.<br/>
<br/>

{{ $is_assessment ? __("test_planned.assignment_text_access_link") : __("test_planned.text_access_link") }}
<a href="{{$directlink}}">
    <strong>{{__("test_planned.this_link")}}</strong>
</a>
@if ($takeCode)
    {{$is_assessment ? __('test_planned.assignment_take_code') : __('test_planned.test_take_code')}}: <strong>{{$takeCode}}</strong>
@endif
<br/>
<br/>

{{ __("test_planned.Bereid je goed voor op deze toets. Succes") }}!<br/>
<br/>
{{ __("test_planned.Test-Correct supportteam") }}
</td>
</tr>
@stop