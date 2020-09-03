<?php


namespace tcCore\Http\Helpers\QtiImporter\v2dot2dot0;


use DOMDocument;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
    public $xml_string;
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
    public $answers = [];
    private $patternMask = false;


    public function __construct(ResourceModel $resource)
    {
        $this->resource = $resource;
        $this->baseDir = pathinfo($resource->href)['dirname'];
        $this->qtiFactory = new QtiFactory($this);

    }

    public function handle()
    {
        $this->loadXMLFromResource();

        try {
            $this->guessItemType();
        } catch (\Exception $e) {
            logger($e->getMessage());
            return false;
        }

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
        $tagNames = ['gapMatchInteraction', 'matchInteraction', 'textEntryInteraction', 'inlineChoiceInteraction', 'choiceInteraction'];

        foreach ($tagNames as $tagName) {
            if (!empty($this->xml->itemBody->xPath('//' . $tagName))) {
                $this->itemType = $tagName;
                continue;
            }
        }
        if (!$this->itemType) {
            throw new \Exception('Could not guess interaction type based on xml');
        }
    }

    public function qtiQuestionTypeToTestCorrectQuestionType($key)
    {
        return $this->qtiFactory->qtiQuestionTypeToTestCorrectQuestionType($key);
    }

    private function handleItemBody()
    {
//        $this->cleanQuestionXmlFromSquareBrackets();
        $this->replaceMultipleChoiceInteraction();
        $this->replaceInlineChoiceInteraction();
        $this->replaceMatchInteraction();
        $this->replaceTextEntryInteraction();
        $this->replaceGapMatchInteraction();

        $dom1 = new DOMDocument("1.0");
        $dom1->preserveWhiteSpace = false;
        $dom1->formatOutput = false;

        $dom1->loadXML($this->xml->itemBody->children()[0]->asXML());

        $this->handleStyling($dom1);


        $this->question_xml = $dom1->saveXML();


    }


    private function replaceMatchInteraction()
    {
        if ($this->itemType === 'matchInteraction') {
            $node = $this->xml->itemBody->xPath('//' . $this->itemType);

            $this->interaction = $node[0]->asXML();

            $dom = dom_import_simplexml($node[0]);
            $dom->parentNode->removeChild($dom);
        }
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

    private function replaceGapMatchInteraction()
    {
        if ($this->itemType === 'gapMatchInteraction') {
            $node = $this->xml->itemBody->xPath('//' . $this->itemType);

            $this->interaction = $node[0]->asXML();

            $dom = dom_import_simplexml($node[0]);
            $dom->parentNode->removeChild($dom);
        }
    }

    protected function getInlineChoiceText($inlineChoice)
    {
        $text = '';
        foreach($inlineChoice->span as $span){
            $text .= $span->__toString();
        }
        return $text;
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
//                        'value' => $inlineChoice->span->__toString(),
                        'value' => $this->getInlineChoiceText($inlineChoice),
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

    private function replaceTextEntryInteraction()
    {
        if ($this->itemType === 'textEntryInteraction') {
            $nodes = $this->xml->itemBody->xPath('//' . $this->itemType);
            $this->interaction = $nodes[0]->asXML();


            foreach ($nodes as $interaction) {
                $result = [];
                $result[] = [
                    'identifier' => $interaction['identifier'],
                    'value' => $interaction->span->__toString(),
                    'correct' => false,
                    'patternMask' => $interaction['patternMask']->__toString()
                ];

                if ($interaction['patternMask']->__toString()) {
                    $this->patternMask = $interaction['patternMask']->__toString();
                }

                $domElement = dom_import_simplexml($interaction);
                $parent = $domElement->parentNode;

                if ($result) {
                    $correctAnswer = $this->responseDeclaration['values'][0];

                    $newNode = $domElement->ownerDocument->createTextNode(sprintf('[%s]', $correctAnswer));
                    $parent->insertBefore($newNode, $domElement);
                }
                $parent->removeChild($domElement);
            }
        }
    }

    private function replaceMathNamespacesAndAddNamespaceDelaractionToDivIdBody()
    {
        if (substr_count($this->xml_string, '</m:')) {
            $this->xml_string = str_replace('<div id="body"', '<div xmlns="http://www.w3.org/1998/Math/MathML" id="body"', $this->xml_string);
            $this->xml_string = str_replace(['<m:', '</m:'], ['<', '</'], $this->xml_string);
        }
    }

    private function loadXMLFromResource()
    {
        // replace xmlns namespace because it
        $this->xml_string = str_replace('xmlns=', 'ns=', file_get_contents($this->resource->href));
        // replace math namespace
        $this->replaceMathNamespacesAndAddNamespaceDelaractionToDivIdBody();


        $this->xml = simplexml_load_string(
            $this->xml_string
        );
    }

    public function getSelectableAnswers()
    {
        if (is_array($this->responseDeclaration['values']) && $count = count($this->responseDeclaration['values'])) {
            return $count;
        }
        return 1;
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
            'selectable_answers' => $this->getSelectableAnswers(),
            'note_type' => "NONE",
            'is_open_source_content' => 0,
            'tags' => [],
            'rtti' => null,
            'test_id' => $this->resource->getTest()->getKey(),
            'user' => Auth::user()->username,//"d1@test-correct.nl",
            'scope' => 'cito',
            'metadata' => $this->getMetadata(),
            'external_id' => $this->resource->identifier,
            'styling' => $this->getStyling(),
        ])->merge(
            $this->mergeExtraTestQuestionAttributes()
        );
        /** @var Response $response */
        $response = (new TestQuestionsController)->store($request);
        if ($response->isSuccessful()) {
            $this->question = $response->original->question;

            $this->handleSimpleChoiceAnswers();
        } else {
            throw new \Exception($response->content());
        }
    }


    private function addAnswer($answer)
    {
        $parsedAnswer = $this->getParsedAnswer($answer['value']);

        $addAnswerRequest = $this->qtiQuestionTypeToTestCorrectQuestionType('class_answer_request')
            ->merge([
                'order' => (string)$answer['order'],
                'answer' => $parsedAnswer,
                'score' => $this->getScore($answer),
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
        $length = strlen($value);
        if ($length >= 255) {
            Log::error(
                sprintf(
                    'answer to long (%s characters) %s [%s]',
                    $length,
                    $this->resource->identifier,
                    $value
                )
            );
        }
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



    private function mergeExtraTestQuestionAttributes()
    {
        if ($this->itemType === 'gapMatchInteraction') {
            $dom = simplexml_load_string($this->interaction);

            $gapTexts = [];
            foreach ($dom->xpath('//gapText') as $gapTextNode) {
                $gapTexts[$gapTextNode['identifier']->__toString()] = $gapTextNode->children()[0]->__toString();
            }

            $loop = 0;
            $answers = [];
            foreach ($dom->xpath('//gap') as $gapNode) {
                $loop++;
                $identifier = $gapNode['identifier']->__toString();
                $gapTextIndex = collect(explode(' ', collect($this->responseDeclaration['values'])->first(function ($gapIdentifierPair) use ($identifier) {
                    return strpos($gapIdentifierPair, $identifier);
                })))->first();
                $gapTextValue = $gapTexts[$gapTextIndex];

                $nodeLinks = $gapNode->xpath('../../../..')[0]->td->p;
                if ($nodeLinks == null) {
                    logger(sprintf('an error occurred while processing %s', $this->resource->href));
                    $links = 'some dummy text (because of import error)';
                } else {
                    $links = $nodeLinks->asXML();
                }

                $answers[] = (object)[
                    'order' => $loop,
                    'left' => $links,
                    'right' => $gapTextValue,
                ];
            }

            return ["answers" => $answers];
        }
        if ($this->itemType === 'matchInteraction') {

            $dom = simplexml_load_string($this->interaction);


            $matchSetNodeList = $dom->xpath('//simpleMatchSet');

            $answers = [];
            $loop = 0;
            foreach ($matchSetNodeList[1] as $answerNode) {
                $answers[] = [
                    'answer' => $answerNode->div->asXML(),
                    'order' => $loop,
                    'indentifier' => $answerNode['identifier']->__toString(),
                ];
                $loop++;
            }

            $subQuestions = [];
            $loop = 0;
            foreach ($matchSetNodeList[0] as $subQuestionNode) {
                $subQuestions[] = [
                    'sub_question' => $subQuestionNode->div->asXML(),
                    'order' => $loop,
                    'score' => 1,
                    'answers' => $this->getAnswersByIdentifier(
                        $subQuestionNode['identifier']->__toString(),
                        $answers
                    ),
                    'identifier' => $subQuestionNode['identifier']->__toString(),
                ];
                $loop++;
            }

            return [
                'answers' => [
                    'answers' => $answers,
                    'subQuestions' => $subQuestions,
                ]
            ];
        }
        return [];
    }

    public function getAnswersByIdentifier($identifier, $answers)
    {
        $answer_pair = collect($this->responseDeclaration['values'])->first(function ($value) use ($identifier) {
            return strstr($value, $identifier);
        });

        $answerIdentifier = trim(str_replace($identifier, '', $answer_pair));
        $correctAnswerForIdentier = collect($answers)->first(function ($answer) use ($answerIdentifier) {
            return $answer['indentifier'] === $answerIdentifier;
        });

        return [$correctAnswerForIdentier['order']];
    }

    /**
     * @param $answerIdentifier
     * @param $correctIdentifier
     * @param $scoreWhenCorrect
     * @param $defaultScore
     * @return mixed
     */
    private function getScore($answer)
    {
        $answerIdentifier = $answer['attributes']['value'];
        $scoreWhenCorrect = $this->responseProcessing['score_when_correct'];

        $defaultScore = $this->responseDeclaration['outcome_declaration']['default_value'];
        return in_array($answerIdentifier, $this->responseDeclaration['values']) ? $scoreWhenCorrect : $defaultScore;
    }

    private function handleSimpleChoiceAnswers(): void
    {
        $el = simplexml_load_string($this->interaction);
        $answers = [];

        $order = 0;
        if ($el) {
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
    }

    public function handleStyling(DOMDocument $dom1)
    {
        $classes = collect(explode(' ', $dom1->documentElement->getAttribute('class')));
        if ($classes->count() > 0) {
            $dom1->documentElement->setAttribute('class', $classes->add('custom-qti-style')->implode(' '));
        }
    }

    public function getStyling()
    {

        return collect($this->stylesheets)->map(function ($path) {
            $pathToStylesheet = sprintf('%s/%s', $this->baseDir, $path['href']);
            if (app()->runningUnitTests()) {
                $pathToStylesheet = sprintf('%s/Test-maatwerktoetsen_v01/aa/%s', $this->baseDir, $path['href']);
            }
            // remove depitems folder;
            $pathToStylesheet = str_replace('/Test-maatwerktoetsen_v01/depitems', '', $pathToStylesheet);
            if ($c = file_get_contents($pathToStylesheet)) {
                return $c;
            };
            throw new \Exception(sprintf('cannot find file %s', $pathToStylesheet));
        })->implode(PHP_EOL);

    }

    private function getMetadata()
    {
        $metaTags = collect([]);
        if ($this->patternMask) {
            $metaTags->add(sprintf('mask:%s', $this->patternMask));
        }

        return $metaTags->implode('|');
    }

    private function cleanQuestionXmlFromSquareBrackets()
    {
        $string = $this->xml->asXML();
        $string = str_replace('[', '<span class="bracket-open"></span>', $string);
        $string = str_replace(']', '<span class="bracket-closed"></span>', $string);
        $this->xml = simplexml_load_string($string);
    }
}
