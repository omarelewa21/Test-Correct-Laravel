<?php

namespace tcCore\Http\Helpers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SvgHelper
{

    const DISK = 'svg-for-drawing-question';
    const SVG_FILENAME = 'template.svg';
    const CORRECTION_MODEL_PNG_FILENAME = 'corretion_model.png';
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

        $this->scaffoldFolderStructure();

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
        $this->updateLayer($value, 'answer-svg');
    }

    public function updateQuestionLayer($value)
    {
        $this->updateLayer($value, 'question-svg');
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
            return $node->getAttribute('class') === $layerName;
        });
        collect($node->childNodes)->each(function ($node) {
            $node->parentNode->removeChild($node);
        });

        $node->appendChild($fragment);
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
    <g  id="grid-preview-svg" stroke="var(--all-BlueGrey)" stroke-width="1"></g>
    <g class="question-svg" ></g>
    <g class="answer-svg" ></g>
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
        $path = sprintf('%s/%s/%s', $this->uuid, $folder ,$identifier);
        $this->disk->put($path, $contents);

        return $identifier;
    }

    private function replaceIdentifiersInImages($value, $layerName)
    {
        $folder = $layerName == 'answer-svg' ? 'answer' : 'question';
        $doc = new \DOMDocument();
        $doc->loadXML(sprintf('<wrap>%s</wrap>', $value));
        collect($doc->getElementsByTagName('image'))->each(function ($node) use ($folder) {
            $path = sprintf('%s/%s/%s', $this->uuid, $folder, $node->getAttribute('identifier'));
            if (!$this->disk->exists($path)) {
                throw new Exception(sprintf('File not found [%s].', $path));
            }
            $image = $this->disk->get($path);
            $node->setAttribute('src','data: '. mime_content_type($this->disk->path($path)).';base64,'.base64_encode($image));
        });

        return substr(substr($doc->saveXML(), 28), 0, -8);
    }

    private function base64DecodeIfNecessary($value)
    {
        if (Str::startsWith($value, '<') && Str::endsWith($value, '>') ) {
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
}