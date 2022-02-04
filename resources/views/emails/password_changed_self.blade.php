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
                <svg width="64" height="64" viewBox="0 0 64 64" xmlns="http://www.w3.org/2000/svg">
                    <g fill="none" fill-rule="evenodd">
                        <circle stroke="#041F74" stroke-width="3" fill="#CEDAF3" cx="35" cy="32" r="30.5"/>
                        <path d="M31.113 61.298c-3.124 3.125-11.356-.041-18.385-7.07-7.03-7.03-10.195-15.261-7.071-18.385l2.828-2.829a2 2 0 0 1 2.829 0l4.95 4.95a2 2 0 0 1 0 2.828l-3 3c.866 1.992 2.238 4.017 4.06 5.84 1.823 1.822 3.848 3.194 5.84 4.06l2.999-3a2 2 0 0 1 2.828 0l4.95 4.95a2 2 0 0 1 0 2.828l-2.828 2.828z" stroke="#041F74" stroke-width="3" fill="#3DBB56"/>
                        <g transform="translate(24 18.5)">
                            <path d="M6.5 16v-6c0-5.523 4.477-10 10-10h5c5.523 0 10 4.477 10 10v6h0" stroke="#041F74" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M14 20.5c.527 2.284 2.789 4 5.5 4s4.973-1.717 5.5-4" stroke="#041F74" stroke-width="2" stroke-linecap="round"/>
                            <ellipse fill="#041F74" cx="15" cy="13" rx="2" ry="2.5"/>
                            <ellipse fill="#041F74" cx="24" cy="13" rx="2" ry="2.5"/>
                            <path d="M19.5 32c5.793 0 10.628-4.105 11.753-9.565.108-.525.19-1.336.247-2.435v-4" stroke="#041F74" stroke-width="3" stroke-linecap="round"/>
                            <rect stroke="#041F74" stroke-width="2" fill="#041F74" x="17" y="30.5" width="5" height="3" rx="1.5"/>
                            <rect stroke="#041F74" stroke-width="2" fill="#004DF5" x="1" y="12.5" width="6" height="8" rx="3"/>
                            <rect stroke="#041F74" stroke-width="2" fill="#004DF5" x="31" y="12.5" width="6" height="8" rx="3"/>
                        </g>
                        <g stroke-linejoin="round">
                            <path d="M35.5 1.5a8 8 0 0 1 8 8v5a8 8 0 0 1-8 8h-9.125l-7.875 7v-7.062a8.001 8.001 0 0 1-7-7.938v-5a8 8 0 0 1 8-8h16z" stroke="#041F74" stroke-width="3" fill="#FFF"/>
                            <g stroke="#004DF5" stroke-linecap="round" stroke-width="2">
                                <path d="M19 8h17M19 12h17M19 16h12"/>
                            </g>
                        </g>
                    </g>
                </svg>
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
