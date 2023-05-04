<?php


namespace tcCore\Http\Helpers\QtiImporter\VersionTwoDotTwoDotZero;


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
use tcCore\Question;
use tcCore\QuestionAttachment;
use tcCore\Test;
use tcCore\TestQuestion;

class QtiParser
{
    protected $originalXML;
    protected $parsedXmlObject;
    protected $qtiAssessmentItem;

    protected $test;
    protected $errors = [];
    protected $volgNr;
    protected $storageDir;
    protected $baseDir;
    protected $testRun;

    public function hasErrors()
    {
        return (bool) count($this->errors);
    }

    public function getErrors()
    {
        return $this->errors;
    }

    protected function addError($question, $error = null, \Exception $e = null){
        if(is_array($question)) $question = (object) $question;

        if($error != null){
            $error = sprintf('Question %s (%s) has an error: %s',$question->question_info->question_title, $question['type'],$error);
        }
        else{
            $error = sprintf('Question %s (%s) has an error: %s',$question->question_info->question_title, $question['type'],$e->getMessage());
        }
        $this->errors[] = $error;
        return $this;
    }

    public function parse($xml)
    {
        try {
            $this->originalXML = $xml;
            $a = simplexml_load_string($xml);
            $this->parsedXmlObject($a);
            $this->qtiAssessmentItem = new QtiAssessmentItem($a->attributes());

            $this->qtiAssessmentItem->setCorrectResponse($this->harvestCorrectResponse());

            $outcomeDetails = collect($this->harvestOutcomeData());
            $this->qtiAssessmentItem->setOutcomeDefaultValueAndType($outcomeDetails->get('value'),$outcomeDetails->get('value'));

            $this->qtiAssessmentItem->setStylesheets($this->harvestStylesheets());

            $this->qtiAssessmentItem->setOriginalBody($this->harvestBody());

        }
        catch(\Exception $e){
            throw $e;
        }
    }

    protected function harvestBody()
    {
        if(property_exists($this->parsedXmlObject,'itemBody')){
            $doc = new DOMDocument;
            $doc->loadxml( $this->originalXML );

            $xpath = new DOMXPath($doc);
            return (string) $xpath->query('/Assessmentitem/itemBody');
        }
        return null;
    }

    protected function harvestStylesheets()
    {
        $return = [];
        if(property_exists($this->parsedXmlObject,'stylesheet')){
            if(is_array($this->parsedXmlObject->stylesheet)){
                foreach($this->parsedXmlObject->stylesheet as $s){
                    $return[] = $s->{@attributes}->href;
                }
            } else {
                $return[] = $this->parsedXmlObject->stylesheet->{@attributes}->href;
            }
        }
        return $return;
    }

    protected function harvestOutcomeData()
    {
        $return = [];
        if(property_exists($this->parsedXmlObject,'outcomeDeclaration') && isset($this->parsedXmlObject->outcomeDeclaration['defaultValue'])){
            $return = [
                'type' => $this->parsedXmlObject->outcomeDeclaration->{@attributes}->baseType,
                'value'=> $this->parsedXmlObject->outcomeDeclaration['defaultValue']
            ];
        }
        return $return;
    }

    protected function harvestCorrectResponse()
    {
        if(property_exists($this->parsedXmlObject,'responseDeclaration') && isset($this->parsedXmlObject->responseDeclaration['correctResponse'])){
            return $this->parsedXmlObject->responseDeclaration['correctResponse']['value'];
        }
        return null;
    }

    public function handle($question, $testRun = false, Test $test, $volgNr = 0, $storageDir, $baseDir)
    {
        $this->testRun = $testRun;
        $this->test = $test;
        $this->volgNr = $volgNr+1; // 1 based instead of 0 based
        try {
            $this->load($question, $storageDir, $baseDir);
//            if(!$testRun) echo "loaded" . PHP_EOL;
            $this->validate($question);
//            if(!$testRun) echo "validated" . PHP_EOL;
            $this->convert();
//            if(!$testRun) echo "converted" . PHP_EOL;
            $this->save($testRun); // if is a testrun then don't add the database transaction, will do in parent
//            if(!$testRun) echo "saved" . PHP_EOL;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function saveCheckedData(Test $test, $volgnr = 1)
    {
        $this->testRun = false;
        $this->test = $test;
        $this->save(false, $volgnr);
    }

    public function checkData($question, Test $test, $storageDir, $baseDir)
    {
        DB::beginTransaction();
        try {
            $this->handle($question,true, $test, 0, $storageDir, $baseDir);
        }
        catch(\Exception $e){
            DB::rollBack();
            $this->addError($question, $e);
            return false;
        }
        DB::rollBack();
        return true;
    }

    public function load($question, $storageDir, $baseDir)
    {
        $this->question = $question;
        $this->storageDir = $storageDir;
        $this->baseDir = $baseDir;
    }

    public function save($withoutTransaction = false, $volgnr = 1)
    {

        if(substr_count($this->convertedAr['question'],'<img src=') > 0){
            try {
                $this->convertedAr['question'] = $this->handleInlineImages($this->convertedAr['question']);
            }
            catch(\Exception $e){
                dd($e);
            }
        }
        if(!$withoutTransaction) DB::beginTransaction();
        try{

            $question = Factory::makeQuestion($this->type);
            if (!$question) {
                throw new QuestionException('Failed to create question with factory', 500);
            }
            $testQuestion = new TestQuestion();
            $testQuestion->fill(array_merge($this->convertedAr, ['order' => $volgnr, 'test_id' => $this->test->getKey()]));
            $test = $testQuestion->test;
            $qHelper = new QuestionHelper();
            $questionData = ['answers' => $this->convertedAr['answer']];


            $totalData = array_merge($this->convertedAr,$questionData);
            $question->fill($totalData);
            Question::setAttributesFromParentModel($question, $test);

            if ($question->save()) {
                $testQuestion->setAttribute('question_id', $question->getKey());
                if ($testQuestion->save()) {
                    $testQuestion->question->addAnswers($testQuestion, $totalData['answer']);

                    $this->addAttachments($testQuestion);
                    $this->addLargeSource($testQuestion);

                }else{
                    throw new QuestionException('Failed to create test question');
                }
            } else {
                throw new QuestionException('Failed to create question');
            }
        }
        catch(\Exception $e){
            if(!$withoutTransaction) DB::rollback();
            throw $e;
            return false;
        }
        if(!$withoutTransaction) DB::commit();
        return true;
    }

    protected function handleInlineImages($question)
    {
        libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        $dom->loadHTML($question, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        $imgs = $dom->getElementsByTagName('img');
        foreach($imgs as $img) {
            $src = $img->getAttribute('src');
            // check if file exists
            $storagePath = sprintf('%s/%s/%s',$this->baseDir,$this->storageDir,$src);
            if(!file_exists($storagePath)){
                throw new QuestionException(sprintf('could not find inline image %s',$storagePath));
            }

            if($this->testRun){ return $question;} // we don't need to actually move the file there

            $file = new UploadedFile($storagePath,basename($src));


            $filename = sprintf('%s-%s.%s',date('YmdHis'),Str::random(10),$file->getExtension());

            $copyStorageDir = storage_path('inlineimages');
            if(!file_exists($copyStorageDir)) {
                mkdir($copyStorageDir, 0777);
            }

            copy ($storagePath,sprintf('%s/%s',$copyStorageDir, $filename));
            $img->setAttribute('src',sprintf('/questions/inlineimage/%s',$filename));
        }
        $html = $dom->saveHTML();
        $dom = null;

        return $html;
    }

    protected function handleAttachments(Testquestion $testQuestion, $attachment){

        if(strlen($attachment) < 3) {
            return true;
        }

        // check if file exists
        $storagePath = sprintf('%s/%s/%s',$this->baseDir,$this->storageDir,$attachment);
        if(!file_exists($storagePath)){
            throw new QuestionException(sprintf('could not find attachment %s',$storagePath));
        }

        if($this->testRun){ return true;} // we don't need to actually move the file there

        $question = $testQuestion->question;

        $file = new UploadedFile($storagePath,basename($attachment));

        $attachmentData = [
            'file' => $file,
            'type' => $file->getType(),
            'title' => basename($attachment),
            'file_name' => time(),
            'file_size' => $file->getSize(),
            'file_extension' => $file->getExtension(),
            'file_mime_type' => mime_content_type($storagePath)
        ];

        $attachment = new Attachment();
        $attachment->fill($attachmentData);
        $attachment->file_name = time();
        $attachment->file_size = $file->getSize();
        $attachment->file_extension = $file->getExtension();
        $attachment->file_mime_type = $file->getMimeType();

        if ($attachment->save() === false) {
            throw new QuestionException(sprintf('Failed to create attachment %s',$storagePath));
        }
        $attachment->file_name = sprintf('%d-%d',time(),$attachment->id);// for safety as we import many files the same second maybe
        $attachment->save();
        copy ($storagePath,sprintf('%s/%s - %s',storage_path('attachments'), $attachment->getKey(),$attachment->getAttribute('file_name')));



        $questionAttachment = new QuestionAttachment();
        $questionAttachment->setAttribute('question_id', $question->getKey());
        $questionAttachment->setAttribute('attachment_id', $attachment->getKey());

        if($questionAttachment->save()) {
            return true;
        } else {
            throw new QuestionException(sprintf('Failed to create question attachment %s',$storagePath));
        }
    }

    protected function addLargeSource(Testquestion $testQuestion){
        $attachment = (string) $this->question->question_content->question_large_source;

        return $this->handleAttachments($testQuestion, $attachment);
    }

    protected function addAttachments(TestQuestion $testQuestion){
        $attachment = (string) $this->question->question_content->question_source;
        return $this->handleAttachments($testQuestion, $attachment);

    }

    protected function generateXmlString($string)
    {
        return "<?xml version='1.0'?><document>".$string."</document>";
    }

    protected function orderAnswersByCorrect($answers){
        return $answers->sortByDesc(function($a,$key){
            return $a['correct'];
        });
    }
}