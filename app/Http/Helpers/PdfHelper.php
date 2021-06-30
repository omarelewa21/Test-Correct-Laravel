<?php

namespace tcCore\Http\Helpers;

class PdfHelper
{
    public static function HtmlToPdf($html) {

        $pdf = app()->make('dompdf.wrapper');
        $pdf->loadHTML($html);
        return $pdf->output();
    }
}
