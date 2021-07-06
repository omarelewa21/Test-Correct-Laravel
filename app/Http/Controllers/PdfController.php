<?php

namespace tcCore\Http\Controllers;

use Facade\FlareClient\Http\Response;
use tcCore\Http\Helpers\PdfHelper;
use tcCore\Http\Requests\HtmlToPdfRequest;

class PdfController extends Controller
{
    /**
     * Converts HTML to a raw PDF
     *
     * @param HtmlToPdfRequest $request
     * @return Response
     */
    public function HtmlToPdf(HtmlToPdfRequest $request)
    {
        $output = PdfHelper::HtmlToPdf($request->get('html'));
        return response($output);
    }
}
