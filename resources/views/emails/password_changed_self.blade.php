@extends('emails.layoutv2')
@section('header_intro')
    <span>Jouw Test-Correct account</span>
@stop
@section('header_message')
    <span>Wachtwoord gewijzigd</span>
@stop

@section('content')
<tr>
<td colspan="999" style="padding-bottom: 40px;
                                            padding-top: 40px;
                                            padding-left: 40px;
                                            padding-right: 40px;
                                            border-left: solid 1px #c3d0ed;
                                            border-right: solid 1px #c3d0ed;">
    <p style="  margin-bottom: 15px;
                font-family: Nunito, sans-serif,Trebuchet MS,Arial;
                font-size: 20px;
                font-weight: bold;
                font-stretch: normal;
                font-style: normal;
                line-height: 1.6;
                letter-spacing: normal;
                color: #041f74;
                margin-top: 0px;
                margin-right: 0px;
                margin-left: 0px;
                padding: 0px;
                ">{{ __("password.Beste") }} {{$user->getNameFullAttribute()}},</p>
    <p style="   font-size: 15px;
                 line-height: 22px;
                 margin-bottom: 15px;
                 margin-top: 0px;
                 margin-right: 0px;
                 margin-left: 0px;
                 padding: 0px;
                 font-family: Nunito, sans-serif,Trebuchet MS,Arial;
                 font-weight: 300;
                 color: #041f74;">Je hebt zojuist het wachtwoord van je Test-Correct account gewijzigd.</p>

    <p style="   font-size: 15px;
                 line-height: 22px;
                 margin-bottom: 15px;
                 margin-top: 0px;
                 margin-right: 0px;
                 margin-left: 0px;
                 padding: 0px;
                 font-family: Nunito, sans-serif,Trebuchet MS,Arial;
                 font-weight: 300;
                 color: #041f74;">Als je deze wijziging niet zelf hebt gemaakt, dan is het verstandig om je wachtwoord te wijzigen voor de veiligheid van je account.</p>
    <table >
        <tr>
            <td style="padding-left: 0px;padding-right: 40px;">
                <img src="{{config('app.base_url')}}img/mail/sticker-neem-contact-op.png">
            </td>
            <td style="padding-left: 0px;padding-right: 40px;">
                <p  style="     font-family: Nunito, sans-serif,Trebuchet MS,Arial;
                                font-size: 16px;
                                font-stretch: normal;
                                font-style: normal;
                                line-height: 1.38;
                                letter-spacing: normal;
                                font-weight: bold;
                                margin-bottom: 0px;
                                margin-top: 0px;
                                margin-right: 0px;
                                margin-left: 0px;
                                padding: 0px;
                                color: #041f74;">Afdeling support</p>
                <p  style="     margin-bottom: 15px;
                                font-family: Nunito, sans-serif,Trebuchet MS,Arial;
                                font-size: 16px;
                                font-weight: normal;
                                font-stretch: normal;
                                font-style: normal;
                                line-height: 1.38;
                                letter-spacing: normal;
                                margin-top: 0px;
                                margin-right: 0px;
                                margin-left: 0px;
                                padding: 0px;
                                color: #041f74;">support@test-correct.nl</p>
            </td>
        </tr>
    </table>
</td>
</tr>
@stop

@section('support')
    @if($user->isA('Student')||$user->isA('Parent'))
        @include('emails.partials.support_student1')
    @elseif($user->isA('Teacher'))
        @include('emails.partials.support1')
    @endif
@stop

@section('footer')
    @include('emails.partials.footer1')
@stop