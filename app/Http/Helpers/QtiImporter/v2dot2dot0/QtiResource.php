<?php


namespace tcCore\Http\Helpers\QtiImporter\v2dot2dot0;


use DOMDocument;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use tcCore\Attachment;
use tcCore\Exceptions\QuestionException;
use tcCore\Http\Helpers\QuestionHelper;
use tcCore\Lib\Question\Factory;
use tcCore\QtiModels\QtiAssessmentItem;
use tcCore\QuestionAttachment;
use tcCore\Test;
use tcCore\TestQuestion;
use tcCore\QtiModels\QtiResource as ResourceModel;

class QtiResource
{
    private $resource;
    private $xml;
    public $attributes = [];
    public $responseDeclaration;
    public $stylesheets = [];
    public $itemBody;
    public $itemType;
    public $interaction;
    public $question;


    public function __construct(ResourceModel $resource)
    {
        $this->resource = $resource;
    }

    public function handle()
    {
        $this->loadXMLFromResource();

        $this->guessItemType();

        $this->handleItemAttributes();
        $this->handleResponseDeclaration();
        $this->handleStyleSheets();

        $this->handleItemBody();

        return $this;
    }

    public function getXML()
    {
        return $this->xml;
    }

    private function handleItemAttributes()
    {
        foreach ($this->xml->attributes() as $key => $value) {
            if ($key === 'ns') return;
            $this->attributes[$key] = (string)$value;
        }
    }

    private function handleResponseDeclaration()
    {
        $declaration = [
            'attributes' => [],
            'correct_response_attributes' => [],
            'values' => [],
            'outcome_declaration' => ['attributes' => []],

        ];

        foreach ($this->xml->responseDeclaration->attributes() as $key => $value) {
            if ($key === 'ns') return false;
            $declaration['attributes'][$key] = (string)$value;
        }

        foreach ($this->xml->responseDeclaration->correctResponse->attributes() as $key => $value) {
            $declaration['correct_response_attributes'][$key] = (string)$value;
        }
        foreach ($this->xml->responseDeclaration->correctResponse->value as $value) {
            $declaration['values'][] = (string)$value;
        }

        foreach ($this->xml->outcomeDeclaration->attributes() as $key => $value) {
            $declaration['outcome_declaration']['attributes'][$key] = (string)$value;
        }

        $declaration['outcome_declaration']['default_value'] = (string)$this->xml->outcomeDeclaration->defaultValue->value;

        $this->responseDeclaration = $declaration;
    }

    private function handleStyleSheets()
    {
        foreach (get_object_vars($this->xml) as $tag => $node) {
            if ($tag === 'stylesheet') {
                foreach ($node as $sheet) {
                    $this->stylesheets[] = [
                        'href' => (string)$sheet['href'],
                        'type' => (string)$sheet['type'],
                    ];
                }
            }
        }
    }

    private function guessItemType()
    {
        $tagNames = ['matchInteraction', 'inlineChoiceInteraction', 'choiceInteraction'];

        foreach ($tagNames as $tagName) {
            if (!empty($this->xml->itemBody->xPath('//' . $tagName))) {
                $this->itemType = $tagName;
                continue;
            }
        }
    }

    private function handleItemBody()
    {
        $itemBody = $this->xml->itemBody;

        $node = $itemBody->xPath('//' . $this->itemType);

        $this->interaction = $node[0]->asXML();

        $dom = dom_import_simplexml($node[0]);
        $dom->parentNode->removeChild($dom);

        $this->question = $itemBody->asXML();
    }

    private function loadXMLFromResource()
    {
        // replace xmlns namespace because it
        $xml_string = str_replace('xmlns=', 'ns=', file_get_contents($this->resource->href));

        $this->xml = simplexml_load_string(
            $xml_string
        );
    }


}
