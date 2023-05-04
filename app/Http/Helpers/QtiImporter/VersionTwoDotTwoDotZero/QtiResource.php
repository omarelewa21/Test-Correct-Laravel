<?php


namespace tcCore\Http\Helpers\QtiImporter\VersionTwoDotTwoDotZero;


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

    private $maxChoices = false;

    /** @var string html part qti located in prompt tags in different s */
    private $promptToBeAddedToItemBody = '';


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
            logger('unknown type in file '.$this->resource->identifier.$e->getMessage());
//            logger($e->getMessage());
            throw new \Exception('unknown type for resource '.$this->resource->identifier.$e->getMessage());
            return false;
        }

        $this->handleResponseProcessing();

        $this->handleItemAttributes();
        $this->handleResponseDeclaration();
        $this->handleStyleSheets();

        $this->handleItemBody();
        $this->handleInlineImages();

        $this->handleQuestion();

        $this->handleExtraAnswersIfNeeded();

        return $this;
    }

    public function getXML()
    {
        return $this->xml;
    }

    private function handleItemAttributes()
    {
        foreach ($this->xml->attributes() as $key => $value) {
            if ($key === 'ns') {
                return;
            }
            $this->attributes[$key] = (string) $value;
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
        if (
            $this->xml->responseProcessing
            && $this->xml->responseProcessing->responseCondition
            && $this->xml->responseProcessing->responseCondition->responseIf
            && $this->xml->responseProcessing->responseCondition->responseIf->setOutcomeValue
            && $this->xml->responseProcessing->responseCondition->responseIf->setOutcomeValue->sum
            && $this->xml->responseProcessing->responseCondition->responseIf->setOutcomeValue->sum->baseValue
        ) {
            $this->responseProcessing['score_when_correct'] = $this->xml->responseProcessing->responseCondition->responseIf->setOutcomeValue->sum->baseValue->__toString();
        }

        if (!array_key_exists('score_when_correct',
                $this->responseProcessing) || empty($this->responseProcessing['score_when_correct'])) {
            $this->responseProcessing['score_when_correct'] = 1;
        }

    }

    private function handleResponseDeclaration()
    {
        foreach ($this->xml->responseDeclaration as $input) {
            $declaration = [
                'attributes'                  => [],
                'correct_response_attributes' => [],
                'values'                      => [],
                'outcome_declaration'         => ['attributes' => []],

            ];

            foreach ($input->attributes() as $key => $value) {
                if ($key === 'ns') {
                    return false;
                }
                $declaration['attributes'][$key] = (string) $value;
            }
            if ($input->correctResponse) {

                foreach ($input->correctResponse->attributes() as $key => $value) {
                    $declaration['correct_response_attributes'][$key] = (string) $value;
                }
                foreach ($input->correctResponse->value as $value) {
                    $declaration['values'][] = (string) $value;
                }
            }

            foreach ($this->xml->outcomeDeclaration->attributes() as $key => $value) {
                $declaration['outcome_declaration']['attributes'][$key] = (string) $value;
            }

            $declaration['outcome_declaration']['default_value'] = (string) $this->xml->outcomeDeclaration->defaultValue->value;

            $this->responseDeclaration[$declaration['attributes']['identifier']] = $declaration;
        }
    }

    private function handleStyleSheets()
    {
        foreach (get_object_vars($this->xml) as $tag => $node) {
            if ($tag === 'stylesheet') {
                foreach ($node as $sheet) {
                    $this->stylesheets[] = [
                        'href' => (string) $sheet['href'],
                        'type' => (string) $sheet['type'],
                    ];
                }
            }
        }
    }

    private function guessItemType()
    {
        $tagNames = [
            'gapMatchInteraction',
            'matchInteraction',
            'textEntryInteraction',
            'inlineChoiceInteraction',
            'choiceInteraction',
            'extendedTextInteraction',
        ];

        foreach ($tagNames as $tagName) {
            if (!empty($this->xml->itemBody->xPath('//'.$tagName))) {
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


        if ($this->xml->itemBody->children()[0] === null) {
            // Woots items can have no itemBody only interaction where question text is inside prompt;
            if ($this->promptToBeAddedToItemBody == '') {
                throw new \Exception(
                    sprintf('No valid question can be constructed with resource %s', $this->resource->href)
                );
            }
            $itemBodyXML = Str::of('<div></div>');

        } else {
            $itemBodyXML = Str::of($this->xml->itemBody->children()[0]->__toString())->trim();
            if($itemBodyXML->length() === 0) {
                $itemBodyXML = Str::of($this->xml->itemBody->children()[0]->asXML());
            };
            $itemBodyXML->prepend('<div>')->append('</div>');
        }
    // because we use loadXML all tags should be closed;
        $dom1->loadXML($itemBodyXML->replace('<br>', '<br/>')->__toString());
        if ($dom1->documentElement === null) {
            logger($itemBodyXML);
            logger($this->xml->itemBody->children()[0]);

            throw new \Exception(sprintf('%s %s %s', __FUNCTION__, __LINE__, $this->resource->href));
        }
        $this->handleStyling($dom1);
        $this->handlePromptToBeAddedToItemBody($dom1);
        $this->question_xml = $dom1->saveXML();
    }

    private function replaceMatchInteraction()
    {
        if ($this->itemType === 'matchInteraction') {
            $node = $this->xml->itemBody->xPath('//'.$this->itemType);

            $this->interaction = $node[0]->asXML();

            $dom = dom_import_simplexml($node[0]);
            $dom->parentNode->removeChild($dom);
        }
    }

    private function replaceMultipleChoiceInteraction()
    {
        if ($this->itemType === 'choiceInteraction') {
            $node = $this->xml->itemBody->xPath('//'.$this->itemType);


            $this->interaction = $node[0]->asXML();;

            $this->maxChoices = $node[0]['maxChoices']->__toString();
            $dom = dom_import_simplexml($node[0]);

            $this->harvestPromptFromSimpleXMLElement($this->xml->itemBody);


            $dom->parentNode->removeChild($dom);
        }
    }

    private function replaceGapMatchInteraction()
    {
        if ($this->itemType === 'gapMatchInteraction') {
            $node = $this->xml->itemBody->xPath('//'.$this->itemType);

            $this->interaction = $node[0]->asXML();

            $dom = dom_import_simplexml($node[0]);
            $dom->parentNode->removeChild($dom);
        }
    }

    protected function getTextEntryInteractionText($el)
    {
        return $this->getInlineChoiceText($el);
    }

    protected function getInlineChoiceText($inlineChoice)
    {
        $doc = new DOMDocument();
        $domElement = $doc->importNode(dom_import_simplexml($inlineChoice), true);
        $doc->appendChild($domElement);
        $text = ($doc->saveHTML());

        return (trim(str_replace(['\r\n', '\n'], '', strip_tags(html_entity_decode($text, ENT_NOQUOTES)))));
    }

    private function replaceInlineChoiceInteraction()
    {
        if ($this->itemType === 'inlineChoiceInteraction') {
            $nodes = $this->xml->itemBody->xPath('//'.$this->itemType);
            $this->interaction = $nodes[0]->asXML();

            foreach ($nodes as $interaction) {
                $id = $interaction['id']->__toString();
                $result = [];
                foreach ($interaction->inlineChoice as $inlineChoice) {

                    $correct = $inlineChoice['identifier']->__toString() === $this->responseDeclaration[$interaction['responseIdentifier']->__toString()]['values'][0];

                    $result[] = [
                        'identifier' => $inlineChoice['identifier'],
//                        'value' => $inlineChoice->span->__toString(),
                        'value'      => $this->getInlineChoiceText($inlineChoice),

                        'correct' => $correct,
                    ];
                }

                $domElement = dom_import_simplexml($interaction);
                $parent = $domElement->parentNode;

                if ($result) {
                    $pipeString = collect($result)->map(function ($response) use ($inlineChoice) {
                        return $response['correct'] ? sprintf('?%s', $response['value']) : $response['value'];
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
            $nodes = $this->xml->itemBody->xPath('//'.$this->itemType);
            $this->interaction = $nodes[0]->asXML();

            foreach ($nodes as $interaction) {
                if (is_array($interaction) && array_key_exists('patternMask',
                        $interaction) && $interaction['patternMask'] && $interaction['patternMask']->__toString()) {
                    $this->patternMask = $interaction['patternMask']->__toString();
                }

                $result = [];
                $result[] = [
                    'identifier'  => $interaction['responseIdentifier'],
                    'value'       => $this->getTextEntryInteractionText($interaction),
                    //$interaction->span->__toString(),
                    'correct'     => false,
                    'patternMask' => $this->patternMask,
                ];


                $domElement = dom_import_simplexml($interaction);
                $parent = $domElement->parentNode;

                if ($result) {

//                    dd([$interaction['responseIdentifier']->__toString(),$this->responseDeclaration ]);
                    $correctAnswer = $this->responseDeclaration[$interaction['responseIdentifier']->__toString()]['values'][0];

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
            $this->xml_string = str_replace('<div id="body"',
                '<div xmlns="http://www.w3.org/1998/Math/MathML" id="body"', $this->xml_string);
            $this->xml_string = str_replace(['<m:', '</m:'], ['<', '</'], $this->xml_string);
        }
    }

    private function loadXMLFromResource()
    {
        // replace xmlns namespace because it
        $this->xml_string = str_replace('xmlns=', 'ns=', file_get_contents($this->resource->href));
        // remove stuff because woots import is polluted.
        $this->xml_string = Str::of($this->xml_string)->replaceLast('<to_str/><to_str/>', '')->trim();

        // replace math namespace
        $this->replaceMathNamespacesAndAddNamespaceDelaractionToDivIdBody();
        $this->xml = simplexml_load_string($this->xml_string);
    }

    public function getSelectableAnswers()
    {
        if ($this->itemType == 'choiceInteraction' && $this->maxChoices) {
            return $this->maxChoices;
        }
//
        if (is_array($this->responseDeclaration) && is_array(array_values($this->responseDeclaration)[0]['values']) && $count = count(array_values($this->responseDeclaration)[0]['values'])) {
            return $count;
        }
        return 1;
    }

    private function handleQuestion()
    {
        $request = new CreateTestQuestionRequest();

        $request->merge([
            'question'               => $this->question_xml,
            'type'                   => $this->qtiQuestionTypeToTestCorrectQuestionType('type'),
            'order'                  => 0,
            'maintain_position'      => "0",
            'discuss'                => "1",
            'score'                  => array_key_exists('score_when_correct',
                $this->responseProcessing) ? $this->responseProcessing['score_when_correct'] : 1,
            'subtype'                => $this->qtiQuestionTypeToTestCorrectQuestionType('subtype'),
            'decimal_score'          => "0",
            'add_to_database'        => 1,
            'attainments'            => [],
            'selectable_answers'     => $this->getSelectableAnswers(),
            'note_type'              => "NONE",
            'is_open_source_content' => 0,
            'tags'                   => [],
            'rtti'                   => null,
            'test_id'                => $this->resource->getTest()->getKey(),
            'user'                   => Auth::user()->username,//"d1@test-correct.nl",
            'scope'                  => 'cito',
            'metadata'               => $this->getMetadata(),
            'external_id'            => $this->resource->identifier,
            'styling'                => $this->getStyling(),
            'published'              => false,
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
                'order'  => (string) $answer['order'],
                'answer' => $parsedAnswer,
                'score'  => $this->getScore($answer),
                'user'   => 'd1@test-correct.nl',
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
//        libxml_use_internal_errors(true);
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
        if ($this->itemType === 'extendedTextInteraction') {
            $answer = '';
            foreach ($this->xml->xpath('//rubricBlock') as $block) {
                if ($block['id']->__toString() === 'qtiAspectInhoudRubricBlock') {
                    $answer = $block->children()[0]->__toString();
                }
            }

            return ['answer' => $answer];
        }

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
                $gapTextIndex = collect(explode(' ',
                    collect(array_values($this->responseDeclaration)[0]['values'])->first(function ($gapIdentifierPair
                    ) use ($identifier) {
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

                $answers[] = (object) [
                    'order' => $loop,
                    'left'  => $links,
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
                    'answer'      => $answerNode->div->asXML(),
                    'order'       => $loop,
                    'indentifier' => $answerNode['identifier']->__toString(),
                ];
                $loop++;
            }

            $subQuestions = [];
            $loop = 0;
            foreach ($matchSetNodeList[0] as $subQuestionNode) {
                $subQuestions[] = [
                    'sub_question' => $this->getParsedAnswer($subQuestionNode->div->asXML()),
                    // $subQuestionNode->div->asXML(),
                    'order'        => $loop,
                    'score'        => 1,
                    'answers'      => $this->getAnswersByIdentifier(
                        $subQuestionNode['identifier']->__toString(),
                        $answers
                    ),
                    'identifier'   => $subQuestionNode['identifier']->__toString(),
                ];
                $loop++;
            }

            return [
                'answers' => [
                    'answers'      => $answers,
                    'subQuestions' => $subQuestions,
                ]
            ];
        }
        return [];
    }

    public function getAnswersByIdentifier($identifier, $answers)
    {
        $answer_pair = collect(array_values($this->responseDeclaration)[0]['values'])->first(function ($value) use (
            $identifier
        ) {
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

        $defaultScore = array_values($this->responseDeclaration)[0]['outcome_declaration']['default_value'];
        return in_array($answerIdentifier,
            array_values($this->responseDeclaration)[0]['values']) ? $scoreWhenCorrect : $defaultScore;
    }

    private function handleSimpleChoiceAnswers(): void
    {
        // woots contains <BR> which is not xml;
        $this->interaction = Str::of($this->interaction)->replace('&lt;br&gt;', '&lt;br/&gt;')->__toString();

        $el = simplexml_load_string($this->interaction);
        $answers = [];

        $order = 0;
        if ($el) {
            foreach ($el->xpath('//simpleChoice') as $tag => $node) {
                $attributes = [];
                foreach ($node->attributes() as $name => $value) {
                    $attributes = [
                        'name'  => $name,
                        'value' => $value->__toString(),
                    ];
                }

                if ($node->children()[0] !== null) {
                    $value = $node->children()[0]->asXML();
                } else {
                    $value = $node->__toString();
                }


                $answer = [
                    'order'      => (string) ++$order,
                    'attributes' => $attributes,
                    'value'      => $value,
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
            global $signal_running_integration_tests;
            if (app()->runningUnitTests() || $signal_running_integration_tests !== null) {
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

    private function handleExtraAnswersIfNeeded()
    {
        if ($this->itemType == 'textEntryInteraction') {
            foreach ($this->responseDeclaration as $declaration) {
                if (array_key_exists('interpretation', $declaration['correct_response_attributes'])) {
                    $answers = explode('#', $declaration['correct_response_attributes']['interpretation']);
                    if ($answers === []) {
                        $answers = explode('|', $declaration['correct_response_attributes']['interpretation']);
                    }

                    foreach ($answers as $answer) {
                        if ($answer != $declaration['values'][0]) {
                            $this->question->addAnswers($this->question->testQuestion,
                                [['answer' => $answer, 'tag' => 1, 'correct' => 1]]);
                        }
                    }
                }
            }
        }
    }

    private function harvestPromptFromSimpleXMLElement(\SimpleXMLElement $element)
    {

        $this->promptToBeAddedToItemBody .= collect($element->xpath('//prompt'))
            ->map(function ($prompt) {
                return $prompt->__toString();
            })->join('');
    }

    /**
     * @param  DOMDocument  $dom1
     */
    private function handlePromptToBeAddedToItemBody(DOMDocument $dom1): void
    {
        if (!empty($this->promptToBeAddedToItemBody)) {
            $fragment = $dom1->createDocumentFragment();
            $fragment->appendXML(
                sprintf(
                    '<div class="question_prompt">%s</div>',
                    $this->promptToBeAddedToItemBody
                )
            );
            $dom1->documentElement->appendChild($fragment);
        }
    }
}
