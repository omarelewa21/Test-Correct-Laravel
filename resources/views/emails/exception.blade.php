@extends('emails.layout')

@section('content')
<tr>
<td colspan="999" style="padding:20px;">
Error: {{ $errMessage }}<br/>
<br/>
{{ $file }} {{ $lineNr }}
<br/>
@if(count($details))
<b>Details</b>
<ul>
@foreach($details as $key => $value)
    <li>
        {{ $key }}<br />
        {!! (is_string($value) ? $value : json_encode($value)) !!} }}
    </li>
@endforeach
</ul>
@endif
<br />
    <b>Server gegevens</b><br />
    <ul>
        @foreach($server as $key => $value)
            <li>
                <b>{{ $key }}</b><br />
                {!! (is_string($value) ? $value : json_encode($value)) !!}
            </li>
        @endforeach
    </ul>
</td>
</tr>
@endsection