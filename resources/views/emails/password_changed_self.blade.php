@extends('emails.layoutv2')
@section('header_intro')
    Jouw Test-Correct account
@stop
@section('header_message')
    Wachtwoord gewijzigd
@stop
@section('applied_styles')
    .aanhef{
        font-family: Nunito;
        font-size: 20px;
        font-weight: bold;
        font-stretch: normal;
        font-style: normal;
        line-height: 1.6;
        letter-spacing: normal;
        color: var(--all-base);
    }
    .padding-top-40{
        padding-top: 40px;
    }
    .padding-bottom-40{
        padding-bottom: 40px;
    }
    .support-title,.support-mail {
        font-family: Nunito;
        font-size: 16px;
        font-weight; normal;
        font-stretch: normal;
        font-style: normal;
        line-height: 1.38;
        letter-spacing: normal;
    }
    .support-title{
        font-weight: bold;
        margin-bottom: 0px;
    }
    .support-line td{
        padding-left: 0px;
    }
@stop

@section('content')
<tr>
<td colspan="999" class="padding-top-40 padding-bottom-40 content-td" >
    <p class="aanhef">{{ __("password.Beste") }} {{$user->getNameFullAttribute()}},</p>
    <p>Je hebt zojuist het wachtwoord van je Test-Correct account gewijzigd.</p>

    <p>Als je deze wijziging niet zelf hebt gemaakt, dan is het verstandig om je wachtwoord te wijzigen voor de veiligheid van je account.</p>
    <table class="support-line">
        <tr>
            <td>
                <img src="{{config('app.base_url')}}img/mail/sticker-neem-contact-op.png">
            </td>
            <td>
                <p class="support-title">Afdeling support</p>
                <p class="support-mail">support@test-correct.nl</p>
            </td>
        </tr>
    </table>
</td>
</tr>
@stop

@section('support')
    @include('emails.partials.support1')
@stop

@section('footer')
    @include('emails.partials.footer1')
@stop