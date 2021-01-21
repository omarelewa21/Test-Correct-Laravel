@extends('emails.layout')

@section('content')
    <tr>
        <td colspan="999" style="padding:20px;">
            Geacht supportteam,<br/>
            <br/>
            docent in demo-school wilt toets/klas uploaden,
            <br/>
                <tr>
                    <td>school_location</td>
                    <td>{{ $demo->school_location }}</td>
                </tr>
                <tr>
                    <td>website_url</td>
                    <td>{{ $demo->website_url }}</td>
                </tr>
                <tr>
                    <td>adres</td>
                    <td>{{ $demo->address }} {{ $demo->house_number }}</td>
                </tr>
                <tr>
                    <td>postcode</td>
                    <td>{{ $demo->postcode }}</td>
                </tr>
                <tr>
                    <td>Plaatsnaam</td>
                    <td>{{ $demo->city }}</td>
                </tr>
                <tr>
                    <td>Aanhef</td>
                    <td>{{ $demo->gender }}</td>
                </tr>
                @if($demo->gender == 'Other')
                    <tr>
                        <td>Aanhef anders</td>
                        <td>{{ $demo->gender_different }}</td>
                    </tr>
                @endif
                <tr>
                    <td>name</td>
                    <td>{{ $demo->name_first }} {{ $demo->name_suffix }} {{ $demo->name }}</td>
                </tr>
                <tr>
                    <td>Email</td>
                    <td>{{ $demo->username }}</td>
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
