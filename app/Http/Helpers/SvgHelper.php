<?php

namespace tcCore\Http\Helpers;

use Illuminate\Support\Facades\Storage;

class SvgHelper
{

    const DISK = 'svg-for-drawing-question';
    const SVG_FILENAME = 'template.svg';
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
    }

    public function updateAnswerLayer($value)
    {
        $doc = new \DOMDocument;
        $doc->loadXML($this->getSvg());
        $fragment = $doc->createDocumentFragment();
        $fragment->appendXML($value);

        foreach ($doc->getElementsByTagName('g') as $node) {
            if ($node->getAttribute('class') === 'answer-svg') {
                $node->appendChild($fragment);
            }
        }
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
}