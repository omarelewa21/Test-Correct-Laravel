@extends('emails.layout')

@section('content')
    <tr>
        <td colspan="999" style="padding:20px;">
            {{ __('uwlr_import_school_location_success.Beste Supportmedewerker') }},<br/>
            <br/>
            <p>
                {{ __('uwlr_import_school_location_success.Zojuist is school locatie :schoolLocationName succesvol geimporteerd via de UWLR koppeling',['schoolLocationName' => $schoolLocationName]) }}
            </p>

            {{ __('uwlr_import_school_location_success.Met vriendelijke groet,') }}<BR>
            {{ __('uwlr_import_school_location_success.Tech') }}<BR>
        </td>
    </tr>
@stop
