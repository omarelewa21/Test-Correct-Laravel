<?php

namespace tcCore\Http\Helpers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SvgHelper
{

    const DISK = 'svg-for-drawing-question';
    const SVG_FILENAME = 'template.svg';
    const CORRECTION_MODEL_PNG_FILENAME = 'correction_model.png';
    const QUESTION_PNG_FILENAME = 'question.png';
    const TRANSPARANT_PIXEL = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+P+/HgAFhAJ/wlseKgAAAABJRU5ErkJggg==';
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

    /**
     * @param string $uuid
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
    }

    public function updateAnswerLayer($value)
    {
        $this->updateLayer($value, 'svg-answer-group');
    }

    public function updateQuestionLayer($value)
    {
        $this->updateLayer($value, 'svg-question-group');
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
        $this->disk->put(
            sprintf('%s/%s', $this->uuid, self::QUESTION_PNG_FILENAME),
            base64_decode($base64EncodedPNG)
        );
    }

    public function updateCorrectionModelPNG($base64EncodedPNG)
    {
        $this->disk->put(
            sprintf('%s/%s', $this->uuid, self::CORRECTION_MODEL_PNG_FILENAME),
            base64_decode($base64EncodedPNG)
        );
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
            <<<XML
<svg viewBox="0 0 0 0"
     class="w-full h-full"
     xmlns="http://www.w3.org/2000/svg"
     style="--cursor-type-locked:var(--cursor-crosshair); --cursor-type-draggable:var(--cursor-crosshair);"
>
    <g  id="svg-preview-group" stroke="var(--all-BlueGrey)" stroke-width="1"></g>
    <g id="svg-question-group" ></g>
    <g id="svg-answer-group" ></g>
</svg>
XML
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

        if($this->disk->exists("$path/$identifier")) {
            return $identifier;
        }

        $this->disk->putFileAs($path, $contents, $identifier);

        return $identifier;
    }

    private function replaceIdentifiersInImages($value, $layerName)
    {
        $folder = $layerName == 'svg-answer-group' ? 'answer' : 'question';
        $doc = new \DOMDocument();
        $doc->loadXML(sprintf('<wrap>%s</wrap>', $value));
        collect($doc->getElementsByTagName('image'))->each(function ($node) use ($folder) {
            $path = sprintf('%s/%s/%s', $this->uuid, $folder, $node->getAttribute('identifier'));
            if (!$this->disk->exists($path)) {
                throw new Exception(sprintf('File not found [%s].', $path));
            }
            $image = $this->disk->get($path);
            $node->setAttribute('href','data:'. mime_content_type($this->disk->path($path)).';base64,'.base64_encode($image));
        });
        return substr(substr($doc->saveXML(), 28), 0, -8);
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
        $this->disk->move($this->uuid, $newUuid);
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
            'x'      => $values[0],
            'y'      => $values[1],
            'width'  => $values[2],
            'height' => $values[3],
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
        return $this->getLayerFromSVG('svg-answer-group', $base64);
    }

    public function getQuestionLayerFromSVG($base64 = false)
    {
        return $this->getLayerFromSVG('svg-question-group', $base64);
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
        $doc->loadXML($this->getSvg());
        $images = $doc->getElementsByTagName('image');
        collect($images)->each(function ($node) {
            $routeName = '';
            if ($node->parentNode->getAttribute('class') === 'answer-svg') {
                $routeName = 'drawing-question.background-answer-svg';
            } else if ($node->parentNode->getAttribute('class') === 'question-svg') {
                $routeName = 'drawing-question.background-question-svg';
            }

            if ($routeName) {
                $url = route(
                    $routeName,
                    ['drawingQuestion' => $this->uuid, 'identifier' => $node->getAttribute('identifier')]
                );

                $node->setAttribute('src', $url);
            }
        });
        return $doc->saveXML();
    }
}