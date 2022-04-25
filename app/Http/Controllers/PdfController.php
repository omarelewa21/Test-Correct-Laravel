<?php

namespace tcCore\Http\Controllers;

use DOMDocument;
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
        $html = $this->absoluteImgPaths($request->get('html'));
        $output = PdfHelper::HtmlToPdf($html);
        return response($output);
    }

    public function getSetting($setting)
    {
        $allowed = ['storage_path'];

        if(in_array($setting,$allowed))
        {
            $return = storage_path();

            return \Illuminate\Support\Facades\Response::make(['status' => $return], 200);

        }

        return Response::make(['status' => ''], 403);
    }

    private function absoluteImgPaths($html)
    {
        $internalErrors = libxml_use_internal_errors(true);
        $doc = new DOMDocument('1.0', 'UTF-8');
        $doc->loadHTML($html);
        libxml_use_internal_errors($internalErrors);
        $imgList = $doc->getElementsByTagName('img');

        foreach ($imgList as $imgNode){
            if(!stristr($imgNode->getAttribute('src'),'inlineimage')){
                continue;
            }
            $basename = basename($imgNode->getAttribute('src'));
            if(stristr($basename,'?')){
                $basename = parse_url($basename)['path'];
            }
            $base64 = base64_encode(file_get_contents(storage_path('inlineimages/'.$basename)));
            $mimtype = mime_content_type(storage_path('inlineimages/'.$basename));
            $srcAttr = sprintf('data:%s;base64,%s',$mimtype,$base64);
            $imgNode->setAttribute('src',$srcAttr);
        }
        $html = $doc->saveHTML($doc->documentElement);
        return $html;
    }
}
