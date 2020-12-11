@extends('emails.onboarding-layout')

@section('content')
        <tr>
            <td colspan="999" class="pl-40 pr-40 pb-40 border-l-r">
                <h5 class="mb-4">Welkom bij Test-Correct</h5>
                <p>Je hebt je aangemeld met het e-mailadres <span class="text-bold">{{$user->username}}</span></p>
                <p>Verifieer je e-mailadres</p>
                <a href="{{ config('app.base_url'). 'user/confirm_email/'.$token}}"
{{--                <a href="{{ 'http://testcorrect.test/user/confirm_email/'.$token}}"--}}
                   class="mt-40 button cta-button stretched text-center svg-stroke-white">Verifieer je e-mailadres
                    <x-icon.arrow></x-icon.arrow>
                </a>
            </td>
        </tr>
        <tr class="footer rounded-b-10">
            <td class="p-40 rounded-b-10 border-all">
                <h5 class="mb-4">Werk volledig digitaal...</h5>
                <table>
                    <tr class="mb-4">
                        <td class="td-img pb-4">
                            <img src="/svg/stickers/toetsen-maken-afnemen.svg" alt="">
                        </td>
                        <td class="td-text px-5">
                            <span class="text-v-top">Toetsen aanmaken en bestaande toetsen omzetten.</span>
                        </td>
                        <td class="td-img">
                            <img src="/svg/stickers/toetsen-beoordelen-bespreken.svg" alt="">
                        </td>
                        <td class="td-text px-5">
                            <span class="text-v-top">Toetsen beoordelen en samen de toets bespreken</span>
                        </td>
                    </tr>
                    <tr>
                        <td class="td-img">
                            <img src="/svg/stickers/klassen.svg" alt="">
                        </td>
                        <td class="td-text px-5">
                            <span class="text-v-top">Klassen maken en uitnodigen om een toets af te nemen</span>
                        </td>
                        <td class="td-img">
                            <img src="/svg/stickers/toetsresultaten-analyse.svg" alt="">
                        </td>
                        <td class="td-text px-5">
                            <span class="text-v-top">Toetsresultaten delen en analystische feedback inzien</span>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

@stop

@section('tfoot')
    <tr>
        <td class="text-center pt-4">
            <span class="block">Heb je je niet aangemeld voor een Test-Correct account?</span>
            <a class="base text-bold" href="" >Annuleer de aanmelding</a>
        </td>
    </tr>
@stop