<?php


namespace tcCore\Http\Helpers\QtiImporter\v2dot2dot0;


use DOMDocument;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use tcCore\Attachment;
use tcCore\Exceptions\QuestionException;
use tcCore\Http\Controllers\TestQuestions\MultipleChoiceQuestionAnswersController;
use tcCore\Http\Controllers\TestQuestionsController;
use tcCore\Http\Helpers\QuestionHelper;
use tcCore\Http\Requests\CreateMultipleChoiceQuestionAnswerRequest;
use tcCore\Http\Requests\CreateTestQuestionRequest;
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
    public $question_xml;
    public $question;
    public $responseProcessing;
    public $images = [];
    public $baseDir;
    public $answersWithImages = [];
    /** @var QtiFactory */
    private $qtiFactory;


    public function __construct(ResourceModel $resource)
    {
        $this->resource = $resource;
        $this->baseDir = pathinfo($resource->href)['dirname'];
        $this->qtiFactory = new QtiFactory($this);
    }

    public function handle()
    {
        $this->loadXMLFromResource();

        $this->guessItemType();

        $this->handleResponseProcessing();

        $this->handleItemAttributes();
        $this->handleResponseDeclaration();
        $this->handleStyleSheets();

        $this->handleItemBody();
        $this->handleInlineImages();
        $this->handleQuestion();

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

    private function handleResponseProcessing()
    {
        $this->responseProcessing = [];

        foreach ($this->xml->xpath('//correctResponse') as $node) {

            if (empty($this->responseProcessing['correct_answer'])) {
                $this->responseProcessing['correct_answer'] = $node->value->__toString();
            } else {
                if (!is_array($this->responseProcessing['correct_answer'])) {
                    $this->responseProcessing['correct_answer'] = [$this->responseProcessing['correct_answer']];

                }
                $this->responseProcessing['correct_answer'][] = $node->value->__toString();
            }
        }
        $this->responseProcessing['score_when_correct'] = $this->xml->responseProcessing->responseCondition->responseIf->setOutcomeValue->sum->baseValue->__toString();
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

    public function qtiQuestionTypeToTestCorrectQuestionType($key)
    {
        return $this->qtiFactory->qtiQuestionTypeToTestCorrectQuestionType($key);
    }

    private function handleItemBody()
    {

        $this->replaceMultipleChoiceInteraction();
        $this->replaceInlineChoiceInteraction();


        $dom1 = new DOMDocument("1.0");
        $dom1->preserveWhiteSpace = false;
        $dom1->formatOutput = false;
        $dom1->loadXML($this->xml->itemBody->children()[0]->asXML());


        $this->question_xml = $dom1->saveXML();
    }

    private function replaceMultipleChoiceInteraction()
    {
        if ($this->itemType === 'choiceInteraction') {
            $node = $this->xml->itemBody->xPath('//' . $this->itemType);

            $this->interaction = $node[0]->asXML();

            $dom = dom_import_simplexml($node[0]);
            $dom->parentNode->removeChild($dom);
        }
    }

    private function replaceInlineChoiceInteraction()
    {
        if ($this->itemType === 'inlineChoiceInteraction') {
            $nodes = $this->xml->itemBody->xPath('//' . $this->itemType);
            $this->interaction = $nodes[0]->asXML();

            foreach ($nodes as $interaction) {
                $id = $interaction['id']->__toString();
                $result = [];
                foreach ($interaction->inlineChoice as $inlineChoice) {
                    $result[] = [
                        'identifier' => $inlineChoice['identifier'],
                        'value' => $inlineChoice->span->__toString(),
                        'correct' => false,
                    ];
                }
                $domElement = dom_import_simplexml($interaction);
                $parent = $domElement->parentNode;

                if ($result) {
                    $pipeString = collect($result)->map(function ($response) {
                        return $response['value'];
                    })->implode('|');
                    $newNode = $domElement->ownerDocument->createTextNode(sprintf('[%s]', $pipeString));
                    $parent->insertBefore($newNode, $domElement);
                }
                $parent->removeChild($domElement);
            }
        }
    }

    private function loadXMLFromResource()
    {
        // replace xmlns namespace because it
        $xml_string = str_replace('xmlns=', 'ns=', file_get_contents($this->resource->href));

        $this->xml = simplexml_load_string(
            $xml_string
        );
    }

    private function handleQuestion()
    {
        $request = new CreateTestQuestionRequest();


        $request->merge([
            'question' => $this->question_xml,
            'type' => $this->qtiQuestionTypeToTestCorrectQuestionType('type'),
            'order' => 0,
            'maintain_position' => "0",
            'discuss' => "1",
            'score' => $this->responseProcessing['score_when_correct'],
            'subtype' => $this->qtiQuestionTypeToTestCorrectQuestionType('subtype'),
            'decimal_score' => "0",
            'add_to_database' => 1,
            'attainments' => [],
            'selectable_answers' => 1,
            'note_type' => "NONE",
            'is_open_source_content' => 1,
            'tags' => [],
            'rtti' => null,
            'test_id' => "1",
            'user' => "d1@test-correct.nl",
        ]);


        $this->question = (new TestQuestionsController)->store($request)->original->question;

        $el = simplexml_load_string($this->interaction);
        $answers = [];

        $order = 0;

        foreach ($el->xpath('//simpleChoice') as $tag => $node) {
            $attributes = [];
            foreach ($node->attributes() as $name => $value) {
                $attributes = [
                    'name' => $name,
                    'value' => $value->__toString(),
                ];
            }
            $answer = [
                'order' => (string)++$order,
                'attributes' => $attributes,
                'value' => $node->children()[0]->asXML(),
            ];
            $this->answers[] = $this->addAnswer($answer);
        }
    }

    private function addAnswer($answer)
    {
        $answerIdentifier = $answer['attributes']['value'];
        $correctIdentifier = $this->responseDeclaration['values'][0];

        $scoreWhenCorrect = $this->responseProcessing['score_when_correct'];

        $defaultScore = $this->responseDeclaration['outcome_declaration']['default_value'];

        $parsedAnswer = $this->getParsedAnswer($answer['value']);

        $addAnswerRequest = $this->qtiQuestionTypeToTestCorrectQuestionType('class_answer_request')
            ->merge([
                'order' => (string)$answer['order'],
                'answer' => $parsedAnswer,
                'score' => $answerIdentifier === $correctIdentifier ? $scoreWhenCorrect : $defaultScore,
                'user' => 'd1@test-correct.nl',
            ]);

        return
            $this->qtiQuestionTypeToTestCorrectQuestionType('class_answer_controller')
                ->store(
                    $this->question->getQuestionInstance()->testQuestions->first(),
                    $addAnswerRequest
                )->original;
    }


    protected function handleInlineImages()
    {
        libxml_use_internal_errors(true);
        $dom = $this->uploadImages($this->question_xml);
        $this->question_xml = $dom->saveHTML();
    }

    private function getParsedAnswer($value)
    {
        libxml_use_internal_errors(true);
        $dom = $this->uploadImages($value);
        $returnValue = $dom->saveHTML();
        $this->answersWithImages[] = $returnValue;
        return $returnValue;
    }

    /**
     * @return DOMDocument
     * @throws QuestionException
     */
    protected function uploadImages($xml): DOMDocument
    {
        $dom = new DOMDocument();
        $dom->loadXML($xml, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        $imgs = $dom->getElementsByTagName('img');
        foreach ($imgs as $img) {
            $src = $img->getAttribute('src');
            // check if file exists
            $storagePath = sprintf('%s/%s', $this->baseDir, $src);
            if (!file_exists($storagePath)) {
                throw new QuestionException(sprintf('could not find inline image %s', $storagePath));
            }

            $file = new UploadedFile($storagePath, basename($src));
            $filename = sprintf('%s-%s.%s', date('YmdHis'), Str::random(10), $file->getExtension());

            $copyStorageDir = storage_path('inlineimages');
            if (!file_exists($copyStorageDir)) {
                mkdir($copyStorageDir, 0777);
            }

            copy($storagePath, sprintf('%s/%s', $copyStorageDir, $filename));
            $imgSrc = sprintf('/questions/inlineimage/%s', $filename);
            $this->images[] = $imgSrc;

            $img->setAttribute('src', $imgSrc);
        }
        return $dom;
    }
}
