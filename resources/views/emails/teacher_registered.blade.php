@extends('emails.layout')

@section('content')
    <tr>
        <td colspan="999" style="padding:20px;">
            Geacht supportteam,<br/>
            <br/>
            Een nieuwe docent heeft zich met onderstaande gegevens aangemeld voor Test-Correct.
            <br/>
            @if($withDuplicateEmailAddress)
                <h3>“Let op! De docent heeft een e-mailadres opgegeven dat al bestaat in de database!"</h3>
                Er is daarom ook geen nieuw account voor deze docent aangemaakt.<br/>
            @endif
            @if($invitedByColleagueWithSameDomain)
                <h3>Uitgenodigd door een collega van hetzelfde domein.</h3>
            @endif
            <table>
                <tr>
                    <td>School Locatie</td>
                    <td>{{ $demo->school_location }}</td>
                </tr>
                <tr>
                    <td>Website Url</td>
                    <td>{{ $demo->website_url }}</td>
                </tr>
                <tr>
                    <td>Adres</td>
                    <td>{{ $demo->address }}</td>
                </tr>
                <tr>
                    <td>Postcode</td>
                    <td>{{ $demo->postcode }}</td>
                </tr>
                <tr>
                    <td>Plaatsnaam</td>
                    <td>{{ $demo->city }}</td>
                </tr>
                <tr>
                    <td>Aanhef</td>
                    @if($demo->gender == 'male')
                        <td>Meneer</td>
                    @elseif($demo->gender == 'female')
                        <td>Mevrouw</td>
                    @elseif($demo->gender == 'different')
                        <td>{{$demo->gender_different}}</td>
                    @endif
                </tr>
                <tr>
                    <td>Naam</td>
                    <td>{{ $demo->name_first }} {{ $demo->name_suffix }} {{ $demo->name }}</td>
                </tr>
                <tr>
                    <td>Email</td>
                    <td>{{ $demo->username }}</td>
                </tr>
                <tr>
                    <td>Afkorting</td>
                    <td>{{ $demo->abbreviation }}</td>
                </tr>
                <tr>
                    <td>Mobiel nummer</td>
                    <td>{{ $demo->mobile }}</td>
                </tr>
                <tr>
                    <td>Vakken (niveau)</td>
                    <td>{{ $demo->subjects }}</td>
                </tr>
                <tr>
                    <td>Hoe kent u ons</td>
                    <td>{{ $demo->how_did_you_hear_about_test_correct }}</td>
                </tr>
                <tr>
                    <td>Opmerkingen</td>
                    <td>{{ $demo->remarks }}</td>
                </tr>
                <tr>
                    <td>Tijdstip van aanmaken</td>
                    <td>{{ $demo->created_at }}</td>
                </tr>
            </table>

            Met vriendelijke groet,<br/>
            Test-Correct supportteam
        </td>
    </tr>
@stop
