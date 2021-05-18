@extends('app');

@section('content')
    <h1>School</h1>
    <table>
        <tr>
            <td>schooljaar</td>
            <td>{{ $school->schooljaar }}</td>
        </tr>
        <tr>
            <td>brincode</td>
            <td>{{ $school->brincode }}</td>
        </tr>
        <tr>
            <td>dependancecode</td>
            <td>{{ $school->dependancecode }}</td>
        </tr>
        <tr>
            <td>aanmaakdatum</td>
            <td>{{ $school->aanmaakdatum }}</td>
        </tr>
        <tr>
            <td>xsdversie</td>
            <td>{{ $school->xsdversie }}</td>
        </tr>
    </table>
    <h1> groepen</h1>
    <table>
        <tr>
            <td>naam</td>
            <td>mutatiedatum</td>
            <td>key</td>
        </tr>
        @foreach($groepen as $groep)
            <tr>
                <td>{{ $groep->naam }}</td>

                <td>{{ $groep->mutatiedatum }}</td>

                <td>{{ $groep->key }}</td>
            </tr>
        @endforeach
    </table>
@endsection
