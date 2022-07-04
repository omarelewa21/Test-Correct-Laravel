<?php

namespace tcCore\Http\Controllers;

use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use DOMDocument;
use Facade\FlareClient\Http\Response;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
//        return $this->wkhtmlToPdf($request);
        $html = $this->base64ImgPaths($request->get('html'));
        $html = $this->svgWirisFormulas($html);
        $output = PdfHelper::HtmlToPdf($html);
        return response($output);
    }

    public function HtmlToPdfFromString($html)
    {
        $html = $this->base64ImgPaths($html);
        $html = $this->svgWirisFormulas($html);
        return $this->snappyToPdfFromString($html);
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


    private function snappyToPdfFromString($html)
    {
        //dump($html);
//        file_put_contents(storage_path('temp/result1.html'),$html);
//        $html = file_get_contents(storage_path('temp/result1.html'));

        $output = \PDF::loadHtml($html)->setOption('header-html', resource_path('pdf_templates/header.html'))->setOption('footer-html', resource_path('pdf_templates/footer.html'));
        return $output->download('file.pdf');


    }

    private function svgWirisFormulas($html)
    {

        while($this->hasMathTag($html)){
            $html = $this->replaceMathTagInHtml($html);
        }
        return $html;
    }

    private function replaceMathNodeWithSvg($mathNode, $doc)
    {
        try {
            $mathNodeString = $doc->saveHtml($mathNode);
            $img = $this->getWirisPngImg($mathNodeString, $doc);
            $mathNode->parentNode->replaceChild($img, $mathNode);
        } catch (\Throwable $th) {
            Bugsnag::notifyException($th);
            return;
        }
    }


    private function getWirisPngImg($mml, $doc)
    {
        $json = $this->getWirisPngFromService($mml);
        $img = $doc->createElement('img');
        $img->setAttribute('width', $json['result']['width']);
        $img->setAttribute('height', $json['result']['height']);
        //$src = sprintf('data:image/png;base64,%s', rawurlencode($json['result']['content']));
        $src = sprintf('data:image/png;base64,%s', $json['result']['content']);
        $img->setAttribute('src', $src);
        $img->setAttribute('style', 'max-width: none; display: inline-block;');
        return $img;
    }

    private function getWirisPngImgString($mml)
    {
        $json = $this->getWirisPngFromService($mml);
        $width = $json['result']['width'];
        $height = $json['result']['height'];
        $src = sprintf('data:image/png;base64,%s', $json['result']['content']);
        return sprintf('<img src="%s" height="%s" width="%s" style="max-width: none; display: inline-block;">',$src,$height,$width);
    }

    private function getWirisSvgImgString($mml)
    {
        $json = $this->getWirisSvgFromService($mml);
        $width = $json['result']['width'];
        $height = $json['result']['height'];
        $src = sprintf('data:image/svg+xml;charset=utf8,%s', rawurlencode($json['result']['content']));
        return sprintf('<img src="%s" height="%s" width="%s" style="max-width: none; display: inline-block;">',$src,$height,$width);
    }


    private function hasMathTag($html)
    {
        if(strpos($html,'<math')>-1){
            return true;
        }
        return false;
    }

    private function replaceMathTagInHtml($html)
    {
        $start = strpos($html,'<math');
        $end = strpos($html,'</math>')+7;
        $length = $end - $start;
        $mml = substr($html,$start,$length);
        $imgString = $this->getWirisPngImgString($mml);
        return str_replace($mml,$imgString,$html);
    }

    private function getWirisPngFromService($mml)
    {
        return $this->getWirisImageFromService($mml,'png');
    }

    private function getWirisSvgFromService($mml)
    {
        return $this->getWirisImageFromService($mml,'svg');
    }

    private function getWirisImageFromService($mml,$type = 'png')
    {
        $folder = 'ckeditor';
        if($type == 'png'){
            $folder = 'ckeditor_png';
        }
        $data = [
            'mml' => $mml,
            'lang' => 'en-gb',
            'metrics' => true,
            'centerbaseline' => false,
            'dpi' => 120,
        ];
        $createPath = sprintf('http://127.0.0.1/%s/plugins/ckeditor_wiris/integration/createimage.php',$folder);
        $path = sprintf('http://127.0.0.1/%s/plugins/ckeditor_wiris/integration/showimage.php',$folder);
        if(stristr(config('app.base_url'),'correct.test')){
            $createPath = sprintf('http://testwelcome.test-correct.test/%s/plugins/ckeditor_wiris/integration/createimage.php',$folder);
            $path = sprintf('http://testwelcome.test-correct.test/%s/plugins/ckeditor_wiris/integration/showimage.php',$folder);
        }
        try {
            $client = new Client();
            $res = $client->request('POST', $createPath, [
                'form_params' => $data,
                'verify' => false]);
            $formulaUrl = $res->getBody()->getContents();
            $components = parse_url($formulaUrl);
            parse_str($components['query'], $results);
            $formula = $results['formula'];
            $data1 = [
                'lang' => 'en-gb',
                'metrics' => true,
                'centerbaseline' => false,
                'formula' => $formula,
                'version' => '7.26.0.1439',
                'dpi' => 120,
            ];
            $res = $client->request('GET', $path, ['query' => $data1]);
            $res = $client->request('POST', $path, [
                'form_params' => $data]);

        } catch (\GuzzleHttp\Exception\RequestException $e) {
            if ($e->hasResponse()) {
                Bugsnag::notifyException($e);
            }
        }
        return json_decode($res->getBody()->getContents(), true);
    }
}
