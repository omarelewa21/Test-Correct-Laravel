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
    public $correctResponse;

    public function __construct(ResourceModel $resource)
    {
        $this->resource = $resource;
    }

    public function handle()
    {
        $this->xml = simplexml_load_string(
            file_get_contents($this->resource->href)
        );

        $this->handleItemAttributes();
        $this->handleCorrectResponse();

        return $this;
    }

    public function getXML() {
        return $this->xml;
    }

    private function handleItemAttributes() {

        foreach($this->xml->attributes() as $key => $value) {
            $this->attributes[$key] = (string) $value;
        }
    }

    private function handleCorrectResponse(){

    }


}
