<?php

namespace tcCore\Http\Helpers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;
use tcCore\DrawingQuestion;

class SvgHelper
{

    const DISK = 'svg-for-drawing-question';
    const SVG_FILENAME = 'template.svg';
    const CORRECTION_MODEL_PNG_FILENAME = 'correction_model.png';
    const QUESTION_PNG_FILENAME = 'question.png';
    const TRANSPARANT_PIXEL = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+P+/HgAFhAJ/wlseKgAAAABJRU5ErkJggg==';
    const SVG_ANSWER_GROUP_ID = 'svg-answer-group';
    const SVG_QUESTION_GROUP_ID = 'svg-question-group';
    private $uuid;
    private $disk;

    /**
     * @param string $uuid
     */
    public function __construct(string $uuid)
    {
        $this->disk = Storage::disk(self::DISK);
        $this->uuid = $uuid;

        if ($this->disk->missing($uuid)) {
            $this->scaffoldFolderStructure();
        }
    }

    private function getQuestionFolder()
    {
        return sprintf('%s/question', $this->uuid);
    }

    private function getAnswerFolder()
    {
        return sprintf('%s/answer', $this->uuid);
    }

    public function getSvg()
    {
        return $this->disk->get(
            sprintf('%s/%s', $this->uuid, self::SVG_FILENAME)
        );

    }

    public function getQuestionSvg($q)
    {
        $bg = "";
        if ($q instanceof DrawingQuestion && $q->getBackgroundImage()) {
            $bg = $this->createQuestionLayerWithLegacyDrawingToolBackground($q);
        }

        if ($this->getQuestionLayerFromSVG()) {
            return base64_encode(Str::of($bg)->append($this->getQuestionLayerFromSVG()));
        }

        return Str::of($bg)->isEmpty() ? null : base64_encode($bg);
    }

    private function createQuestionLayerWithLegacyDrawingToolBackground($q)
    {
        [$width, $height, $identifier] = $this->getOldDrawingQuestionLayerData($q);
        
        $doc = (new \DOMDocument);

        $groupElement = $doc->createElement('g');
        $groupElement->setAttribute('class', 'shape draggable');
        $groupElement->setAttribute('id', 'image-1');

        $imageElement = $doc->createElement('image');
        $imageElement->setAttribute('class', 'main');
        $imageElement->setAttribute('href', $q->getBackgroundImage());
        $imageElement->setAttribute('identifier', $identifier);
        $imageElement->setAttribute('width', $width);
        $imageElement->setAttribute('height', $height);

        $imageElement->setAttribute('y', '-' . $height / 2);          // height/2  => to centralize the image y value so that the center of the image is at the center of the drawing tool
        $imageElement->setAttribute('x', '-' . $width / 1.5);         // width/1.5 => because there is a sidebar in the drawing tool, then, value should be less than 2 to avoid image under sidebar

        $groupElement->appendChild($imageElement);
        $doc->appendChild($groupElement);

        $layerHtml = $doc->saveHTML();

        return $layerHtml;
    }

    private function getOldDrawingQuestionLayerData($q){
        $dir = sprintf('%s/%s', Storage::disk(self::DISK)->path(''), $this->getQuestionFolder());
        
        $image_exists = false;
        if(is_readable($dir) && count(scandir($dir)) > 2){
            // if image exists in question folder, then retreive the image data
            foreach(scandir($dir) as $file){
                if(Uuid::isValid($file)){
                    [$width, $height] = getimagesize(sprintf('%s/%s', $dir, $file));
                    $identifier = $file;
                    $image_exists = true;
                    break;
                }
            }
        }
        if(!$image_exists){
            // if image doesn't exist in question folder, then create a new question image
            [$width, $height] = getimagesize($q->getCurrentBgPath());
            $identifier = Uuid::uuid4();

            // Todo => delete previous images exists in the question folder
            $this->addImageToLayer('question', $identifier, $q->getCurrentBgPath());
        }

        return [$width, $height, $identifier];
    }

    public function createِAnswerLayerForOldQuestion(DrawingQuestion $q)
    {
        [$width, $height] = getimagesize($q->answer);
        $identifier = Uuid::uuid4();

        // Todo => delete previous images exists in the question folder
        $this->addImageToLayer('answer', $identifier, $q->answer);

        $doc = (new \DOMDocument);

        $groupElement = $doc->createElement('g');
        $groupElement->setAttribute('class', 'shape draggable');
        $groupElement->setAttribute('id', 'image-1');

        $imageElement = $doc->createElement('image');
        $imageElement->setAttribute('class', 'main');
        $imageElement->setAttribute('href', $q->answer);
        $imageElement->setAttribute('identifier', $identifier);
        $imageElement->setAttribute('width', $width * 5 / 6);
        $imageElement->setAttribute('height', $height);
        $imageElement->setAttribute('x', '-' . $width / 2);
        $imageElement->setAttribute('y', '-' . $height / 2);

        $groupElement->appendChild($imageElement);
        $doc->appendChild($groupElement);

        $layerHtml = $doc->saveHTML();

        return base64_encode($layerHtml);
    }


    /**
     * @return void
     */
    public function scaffoldFolderStructure(): void
    {
        $this->disk->makeDirectory($this->uuid);
        $this->disk->makeDirectory($this->getQuestionFolder());
        $this->disk->makeDirectory($this->getAnswerFolder());
        $this->initSVG();
        $this->initCorrectionModelPNG();
        $this->initQuestionPNG();

        if (strstr($this->uuid, 'temp-')) {
            $folderName = str_replace('temp-', '', $this->uuid);
            if ($this->disk->exists($folderName)) {

                foreach ($this->disk->allFiles($folderName) as $fileOrDirectory) {
                    $destination = str_replace($folderName, $this->uuid, $fileOrDirectory);
                    if ($this->disk->exists($destination)) {
                        $this->disk->delete($destination);
                    }
                    $this->disk->copy($fileOrDirectory, $destination);
                }
            }
        }

    }

    public function updateAnswerLayer($value)
    {
        $this->updateLayer($value, self::SVG_ANSWER_GROUP_ID);
    }

    public function updateQuestionLayer($value)
    {
        $this->updateLayer($value, self::SVG_QUESTION_GROUP_ID);
    }

    private function initCorrectionModelPNG()
    {
        $this->disk->put(
            sprintf('%s/%s', $this->uuid, self::CORRECTION_MODEL_PNG_FILENAME),
            base64_decode(self::TRANSPARANT_PIXEL)
        );
    }

    public function getCorrectionModelPNG()
    {
        return $this->disk->get(
            sprintf('%s/%s', $this->uuid, self::CORRECTION_MODEL_PNG_FILENAME)
        );
    }

    public function getQuestionModelPNG()
    {
        return $this->disk->get(
            sprintf('%s/%s', $this->uuid, self::QUESTION_PNG_FILENAME)
        );
    }


    public function updateQuestionPNG($base64EncodedPNG)
    {
        $base64EncodedPngWithoutHeader = preg_replace('#^data:image/[^;]+;base64,#', '', $base64EncodedPNG);


        $this->disk->put(
            sprintf('%s/%s', $this->uuid, self::QUESTION_PNG_FILENAME),
            base64_decode($base64EncodedPngWithoutHeader)
        );

        $server = \League\Glide\ServerFactory::create([
            'source' => Storage::disk(SvgHelper::DISK)->path(sprintf('%s', $this->uuid)),
            'cache'  => Storage::disk(SvgHelper::DISK)->path(sprintf('%s/cache', $this->uuid))
        ]);


        $server->deleteCache(self::QUESTION_PNG_FILENAME);
    }

    public function updateCorrectionModelPNG($base64EncodedPNG)
    {
        $base64EncodedPngWithoutHeader = preg_replace('#^data:image/[^;]+;base64,#', '', $base64EncodedPNG);

        $path = sprintf('%s/%s', $this->uuid, self::CORRECTION_MODEL_PNG_FILENAME);
        $this->disk->put(
            $path,
            base64_decode($base64EncodedPngWithoutHeader)
        );

        $server = \League\Glide\ServerFactory::create([
            'source' => Storage::disk(SvgHelper::DISK)->path(sprintf('%s', $this->uuid)),
            'cache'  => Storage::disk(SvgHelper::DISK)->path(sprintf('%s/cache', $this->uuid))
        ]);


        $server->deleteCache(self::CORRECTION_MODEL_PNG_FILENAME);
    }

    private function updateLayer($value, $layerName)
    {
        $value = $this->base64DecodeIfNecessary($value);
        $doc = new \DOMDocument;
        $doc->loadXML($this->getSvg());

        $fragment = $doc->createDocumentFragment();
        $fragment->appendXML(
            $this->replaceIdentifiersInImages($value, $layerName)
        );

        $node = collect($doc->getElementsByTagName('g'))->first(function ($node) use ($layerName) {
            return $node->getAttribute('id') === $layerName;
        });
        collect($node->childNodes)->each(function ($node) {
            $node->parentNode->removeChild($node);
        });

        collect($fragment->childNodes)->each(function ($fragnode) use ($node) {
            $node->appendChild($fragnode);
        });

        $this->saveSVG($doc->saveXML());
    }

    private function initSVG()
    {
        $this->saveSVG(
            "<svg viewBox=\"0 0 0 0\"
                 class=\"w-full h-full\"
                 xmlns=\"http://www.w3.org/2000/svg\"
                 style=\"--cursor-type-locked:var(--cursor-crosshair); --cursor-type-draggable:var(--cursor-crosshair);\"
            >
                <g  id=\"svg-preview-group\" stroke=\"var(--all-BlueGrey)\" stroke-width=\"1\"></g>
                <g id=\"" . self::SVG_QUESTION_GROUP_ID . "\" ></g>
                <g id=\"" . self::SVG_ANSWER_GROUP_ID . "\" ></g>
            </svg>"
        );
    }

    public function saveSVG($xml)
    {
        $this->disk->put(
            sprintf('%s/%s', $this->uuid, self::SVG_FILENAME), $xml
        );
    }

    private function initQuestionPNG()
    {
        $this->disk->put(
            sprintf('%s/%s', $this->uuid, self::QUESTION_PNG_FILENAME),
            base64_decode(self::TRANSPARANT_PIXEL)
        );
    }

    public function addQuestionImage($identifier, $contents)
    {

        return $this->addImageToLayer('question', $identifier, $contents);
    }

    public function addAnswerImage($identifier, $contents)
    {
        return $this->addImageToLayer('answer', $identifier, $contents);
    }

    public function addImageToLayer($layer, $identifier, $contents)
    {
        $folder = $layer == 'answer' ? 'answer' : 'question';

//        $identifier = (string)Str::uuid();
        $path = sprintf('%s/%s', $this->uuid, $folder);

        if ($this->disk->exists("$path/$identifier")) {
            return $identifier;
        }

        $this->disk->putFileAs($path, $contents, $identifier);

        return $identifier;
    }

    private function replaceIdentifiersInImages($value, $layerName)
    {
        $folder = $layerName == self::SVG_ANSWER_GROUP_ID ? 'answer' : 'question';
        $doc = new \DOMDocument();
        $doc->loadXML(sprintf('<wrap>%s</wrap>', $value));
        collect($doc->getElementsByTagName('image'))->each(function ($node) use ($folder) {
            $path = sprintf('%s/%s', $this->uuid, $folder);
            if (!$this->disk->exists($path)) {
                throw new Exception(sprintf('File not found [%s].', $path));
            }
            $image = $this->getCompressedImage($path, $node->getAttribute('identifier'));
            $node->setAttribute('href', $image);//'data:' . mime_content_type($image) . ';base64,' . base64_encode($image));
        });

        return substr(substr($doc->saveXML(), 28), 0, -8);
    }

    private function getCompressedImage($path, $file)
    {

        $server = \League\Glide\ServerFactory::create([
            'source' => Storage::disk(self::DISK)->path($path),
            'cache'  => Storage::disk(self::DISK)->path(sprintf('%s/cache', $path)),

        ]);

        $widthAndHeight = $this->getArrayWidthAndHeight();

        $height = (float)$widthAndHeight['h'];
        $width = (float)$widthAndHeight['w'];

        if ($width > 800) {
            $width = 800;
        }

        if ($height > 0 && $widthAndHeight['w'] > 0) {
            $height = round(800 * $height / $widthAndHeight['w']);
        }


        $widthAndHeight['h'] = (string)$height;
        $widthAndHeight['w'] = (string)$width;

        return $server->getImageAsBase64($file, $widthAndHeight + ['q' => '25',]);
    }

    private function base64DecodeIfNecessary($value)
    {
        if (Str::startsWith($value, '<') && Str::endsWith($value, '>')) {
            return $value;
        }
        return base64_decode($value);
    }

    public function rename($newUuid)
    {
        if (!$newUuid) {
            throw new \Exception('No uuid provided');
        }
        if ($this->disk->missing($newUuid)) {
            $this->disk->makeDirectory($newUuid);
        }

        foreach ($this->disk->allDirectories($this->uuid) as $directory) {
            $destination = str_replace($this->uuid, $newUuid, $directory);
            if ($this->disk->exists($destination)) {
                $this->disk->deleteDirectory($destination);
            }
            $this->disk->makeDirectory($destination);
        }

        foreach ($this->disk->allFiles($this->uuid) as $fileOrDirectory) {
            $destination = str_replace($this->uuid, $newUuid, $fileOrDirectory);

            if ($this->disk->exists($destination)) {
                $this->disk->delete($destination);
            }
            $this->disk->copy($fileOrDirectory, $destination);
        }

//        $this->disk->copyDirectory($this->uuid, $newUuid);
        $this->uuid = $newUuid;

    }

    public function setViewBox(array $viewBox)
    {
        $doc = new \DOMDocument;
        $doc->loadXML($this->getSvg());
        $svgNode = collect($doc->getElementsByTagName('svg'))->first();
        $svgNode->setAttribute('viewBox', $this->makeViewBoxString($viewBox));

        $this->saveSVG($doc->saveXML());
    }

    /**
     * @param array $viewBox
     * @return string
     */
    public function makeViewBoxString(array $viewBox): string
    {
        return sprintf('%s %s %s %s',
            $viewBox['x'],
            $viewBox['y'],
            $viewBox['width'],
            $viewBox['height']
        );
    }

    /**
     * @param string $viewBox
     * @return array
     */
    public function makeViewBoxArray(string $viewBox): array
    {
        $values = Str::of($viewBox)->explode(' ');
        return [
            'x'      => (float)$values[0],
            'y'      => (float)$values[1],
            'width'  => (float)$values[2],
            'height' => (float)$values[3],
        ];
    }

    public function getViewBox()
    {
        $doc = new \DOMDocument;
        $doc->loadXML($this->getSvg());
        $svgNode = collect($doc->getElementsByTagName('svg'))->first();
        return $svgNode->getAttribute('viewBox');
    }

    public function getAnswerLayerFromSVG($base64 = false)
    {
        return $this->getLayerFromSVG(self::SVG_ANSWER_GROUP_ID, $base64);
    }

    public function getQuestionLayerFromSVG($base64 = false)
    {
        return $this->getLayerFromSVG(self::SVG_QUESTION_GROUP_ID, $base64);
    }

    private function getLayerFromSVG($layerName, $base64 = false)
    {
        $doc = new \DOMDocument;
        $doc->loadXML($this->getSvg());
        $layer = collect($doc->getElementsByTagName('g'))->first(function ($node) use ($layerName) {
            return $node->getAttribute('id') === $layerName;
        });
        $layerHtml = $this->trimParentTagFromLayer($doc->saveHTML($layer));

        return $base64 ? base64_encode($layerHtml) : $layerHtml;
    }

    private function trimParentTagFromLayer($layer)
    {
        return substr(strstr(ltrim($layer, '<'), '<'), 0, -4);
    }

    public function getSvgWithUrls()
    {
        $doc = new \DOMDocument();
        $doc->validateOnParse = true;
        $doc->loadXML($this->getSvg());


        collect([self::SVG_ANSWER_GROUP_ID, self::SVG_QUESTION_GROUP_ID])->each(function ($layer) use ($doc) {
            $parentNode = collect($doc->getElementsByTagName('g'))->first(function ($node) use ($layer) {
                return $node->getAttribute('id') == $layer;
            });

            if ($parentNode) {
                $images = $parentNode->getElementsByTagName('image');;
                collect($images)->each(function ($node) use ($doc, $layer) {
                    $routeName = '';
                    if ($layer === self::SVG_ANSWER_GROUP_ID) {

                        $routeName = 'drawing-question.background-answer-svg';
                    } else if ($layer === self::SVG_QUESTION_GROUP_ID) {

                        $routeName = 'drawing-question.background-question-svg';
                    }

                    if ($routeName) {
                        $url = route(
                            $routeName,
                            ['drawingQuestion' => $this->uuid, 'identifier' => $node->getAttribute('identifier')]
                        );
                        $node->setAttribute('href', $url);
                    }
                });
            }
        });
        return $doc->saveXML();
    }

    public function getArrayWidthAndHeight()
    {
        list($x, $y, $width, $height) = sscanf($this->getViewBox(), '%s %s %s %s');

        return ['w' => $width, 'h' => $height];
    }

    private function isOldDrawing()
    {
        if (Str::contains($this->uuid, 'temp')) {
            $questionUuid = Str::after($this->uuid, 'temp-');
        } else {
            $questionUuid = $this->uuid;
        }
        if (DrawingQuestion::whereUuid($questionUuid)->exists()) {
            $question = DrawingQuestion::whereUuid($questionUuid)->first();
            return filled($question->answer) && blank($question->zoom_group);
        }

        return false;
    }

    public function delete()
    {
        $this->disk->deleteDirectory($this->uuid);
    }
}