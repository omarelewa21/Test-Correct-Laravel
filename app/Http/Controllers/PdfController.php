<?php

namespace tcCore\Http\Controllers;

use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use DOMDocument;
use Facade\FlareClient\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use mikehaertl\wkhtmlto\Pdf;
use tcCore\Http\Helpers\PdfHelper;
use tcCore\Http\Requests\HtmlToPdfRequest;

class PdfController extends Controller
{
    const DISK = 'pdf_images';

    /**
     * Converts HTML to a raw PDF
     *
     * @param HtmlToPdfRequest $request
     * @return Response
     */
    public function HtmlToPdf(HtmlToPdfRequest $request)
    {
        $html = $this->base64ImgPaths($request->get('html'));
        file_put_contents(storage_path('temp/result2.html'),$html);
        $output = PdfHelper::HtmlToPdf($html);
        return response($output);
    }

    public function HtmlToPdfFromString($html)
    {
        $html = $this->base64ImgPaths($html);
//        return $this->wkhtmlToPdfFromString($html);
        file_put_contents(storage_path('temp/result1.html'),$html);
        return PdfHelper::HtmlToPdf($html);
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

    private function base64ImgPaths($html)
    {
        $internalErrors = libxml_use_internal_errors(true);
        $doc = new DOMDocument('1.0', 'UTF-8');
        $doc->loadHTML($html);
        libxml_use_internal_errors($internalErrors);
        $imgList = $doc->getElementsByTagName('img');
        foreach ($imgList as $imgNode){
            $this->getInlineImageBase64ImgPath($imgNode);
            $this->getImageLoadBase64ImgPath($imgNode);
        }
        $html = $doc->saveHTML($doc->documentElement);
        return $html;
    }

    private function getInlineImageBase64ImgPath($imgNode)
    {
        if(!stristr($imgNode->getAttribute('src'),'inlineimage')){
            return;
        }
        $baseName = basename($imgNode->getAttribute('src'));
        if(stristr($baseName,'?')){
            $baseName = parse_url($baseName)['path'];
        }
        try {
            if (Storage::disk('cake')->exists('questionanswers/'.$baseName)) {
                $diskName = 'cake';
                $prefix = 'questionanswers/';
                return $this->getBase64ImgPath($imgNode,$baseName,$diskName,$prefix);
            }
            $diskName = 'inline_images';
            $this->getBase64ImgPath($imgNode,$baseName,$diskName);
        }catch (\Throwable $th) {
            Bugsnag::notifyException($th);
            return;
        }
    }

    private function getImageLoadBase64ImgPath($imgNode)
    {
        if(!stristr($imgNode->getAttribute('src'),'imageload.php')){
            return;
        }
        try{
            parse_str(parse_url($imgNode->getAttribute('src'))['query'], $params);
            $baseName = $params['filename'];
            $diskName = 'cake';
            $prefix = 'questionanswers/';
            $this->getBase64ImgPath($imgNode,$baseName,$diskName,$prefix);
        }catch (\Throwable $th) {
            Bugsnag::notifyException($th);
            return;
        }
    }

    private function getBase64ImgPath($imgNode,$baseName,$diskName,$prefix='')
    {
        $base64 = $this->getCompressedImage($diskName,$prefix, $baseName, $imgNode);
        $imgNode->setAttribute('src', $base64);
    }

    private function getCompressedImage($diskName,$path, $file, $imgNode) {
        $server = \League\Glide\ServerFactory::create([
            'source' => Storage::disk($diskName)->path($path),
            'cache' => Storage::disk(self::DISK)->path(sprintf('%s/cache', $path)),
        ]);
        $width = 800;
        if($imgNode->hasAttribute('width')&&($imgNode->getAttribute('width')<800)){
            $width = (int) $imgNode->getAttribute('width');
        }
        $widthHeight = ['w' => $width];
        $filesize = filesize(Storage::disk($diskName)->path(sprintf('%s/%s', $path, $file)));
        switch (true) {
            case $filesize <= 500000:
                $quality = '100';
                break;
            default:
                $quality = '25';
                break;
        }

        if($imgNode->hasAttribute('width')&&$imgNode->hasAttribute('height')){
            $height = round(800*($imgNode->getAttribute('height')/$imgNode->getAttribute('width')));
            $widthHeight = ['w' => $width,'h' => (int) $height];
            $imgNode->removeAttribute('width');
            $imgNode->removeAttribute('height');
        }
        return $server->getImageAsBase64($file, $widthHeight+['fit'=>'contain',  'fm' => 'jpg', 'q' => $quality,]);
    }

    private function wkhtmlToPdf(HtmlToPdfRequest $request)
    {
        $html = $this->base64ImgPaths($request->get('html'));
        file_put_contents(storage_path('temp/result1.html'),$html);
        $options = [
            'disable-javascript',
            'header-html'=> storage_path('temp/header.html'),
        ];
        $pdf = new Pdf($options);
        $pdf->addPage($html);
        $outputPath = storage_path('temp/result1.pdf');
        $pdf->saveAs($outputPath);
        $output = $pdf->toString();

        return response()->make($output, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="toets.pdf"'
        ]);
    }

    private function wkhtmlToPdfFromString($html)
    {
        file_put_contents(storage_path('temp/result1.html'),$html);
        $options = [
            'disable-javascript',
            'header-html'=> storage_path('temp/header.html'),
            'load-error-handling'=>'ignore',
            'enable-local-file-access'=> true,
        ];
        $pdf = new Pdf($options);
        $pdf->addPage($html);
        $outputPath = storage_path('temp/result1.pdf');
        $pdf->saveAs($outputPath);
        $output = $pdf->toString();
        dump($pdf->getError());
        return $output;

    }
}
