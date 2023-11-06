<?php

namespace tcCore\Factories\Questions;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Livewire\TemporaryUploadedFile;
use tcCore\Attainment;
use tcCore\Exceptions\QuestionException;
use tcCore\Factories\Interfaces\FactoryQuestion as FactoryQuestionInterface;
use tcCore\GroupQuestionQuestion;
use tcCore\Http\Controllers\TestQuestions\AttachmentsController;
use tcCore\Http\Requests\CreateAttachmentRequest;
use tcCore\Lib\Question\Factory;
use tcCore\Lib\Repositories\PValueTaxonomyBloomRepository;
use tcCore\Lib\Repositories\PValueTaxonomyMillerRepository;
use tcCore\Lib\Repositories\PValueTaxonomyRTTIRepository;
use tcCore\Question;
use tcCore\Test;
use tcCore\TestQuestion;
use tcCore\User;

abstract class FactoryQuestion implements FactoryQuestionInterface
{
    protected $testModel;
    protected $questionProperties;
    public $lastTestQuestion;
    protected Collection $videos;
    protected Collection $uploads;
    protected array $audioUploadOptions = [];

    public static function create()
    {
        $factory = new static;

        $factory->questionProperties = $factory->definition();
        $factory->videos = collect();
        $factory->uploads = collect();

        return $factory;
    }

    public function setProperties(array $properties)
    {
        if (!$properties) {
            throw new \Exception("Adding open a properties array is required,\n without 'setProperties' the Question Factory uses default values");
        }

        $this->questionProperties = array_merge($this->questionProperties, $properties);

        return $this;
    }

    public function addRandomAttainmentsBySubject()
    {
        if ($this->questionProperties['attainments'] == []) {
            $attainments = [];
            $randomAttainmentForBaseSubject = Attainment::where(
                'base_subject_id',
                $this->testModel->subject->base_subject_id
            )->whereNull('attainment_id')
                ->where('education_level_id', $this->testModel->education_level_id)
                ->pluck('id')
                ->whenNotEmpty(fn($q) => $q->random(1))
                ->first();


            if ($randomAttainmentForBaseSubject) {
                $attainments[] = $randomAttainmentForBaseSubject;


                $subattainment = Attainment::where('attainment_id', $randomAttainmentForBaseSubject)
                    ->pluck('id')
                    ->whenNotEmpty(fn($q) => $q->random(1))
                    ->first();
                if ($subattainment) {
                    $attainments[] = $subattainment;
                }

                $this->questionProperties = array_merge($this->questionProperties, [
                    'attainments' => $attainments
                ]);
            }
        }
        return $this;
    }

    public function addRandomTaxonomy($rtti = true, $miller = true, $bloom = true)
    {
        $taxonomy = [];
        if ($this->questionProperties['rtti'] == "" && $rtti) {
            $taxonomy['rtti'] = collect(PValueTaxonomyRTTIRepository::OPTIONS)->random();
        }
        if ($this->questionProperties['miller'] == "" && $miller) {
            $taxonomy['miller'] = collect(PValueTaxonomyMillerRepository::OPTIONS)->random();
        }
        if ($this->questionProperties['bloom'] == "" && $bloom) {
            $taxonomy['bloom'] = collect(PValueTaxonomyBloomRepository::OPTIONS)->random();
        }

        $this->questionProperties = array_merge($this->questionProperties, $taxonomy);

        return $this;
    }

    public function store()
    {
        $this->addRandomAttainmentsBySubject();
        $this->addRandomTaxonomy();

        $this->questionProperties = array_merge(
            $this->questionProperties,
            ['test_id' => $this->testModel->id],
            ['owner_id' => $this->testModel->owner_id],
            $this->calculatedQuestionProperties(),
        );

        //don't store subquestions as testQuestion:
        if(!isset($this->questionProperties['is_subquestion']) || !$this->questionProperties['is_subquestion']) {
            //store when all properties and answers are added
            $this->lastTestQuestion = $this->doWhileLoggedIn(function () {
                return TestQuestion::store($this->questionProperties);
            }, User::find($this->testModel->author_id));
        }

        $this->handleAfterStoreActions();
    }

    public function setScore(int $score)
    {
        $this->questionProperties['score'] = $score;

        return $this;
    }

    protected function calculatedQuestionProperties(): array
    {
        return [];
    }

    public function setTestModel(Test $testModel)
    {
        $this->testModel = $testModel;

        return $this;
    }

    public function handleAttachments()
    {
        if (isset($this->uploads)) {
            $this->doWhileLoggedIn(function () {
                $this->uploads->each(function ($upload) {
                    switch ($upload->getMimeType()) {
                        case 'image/png':
                            $fileName = 'AttachmentFactoryImage.png';
                            break;
                        case 'image/gif':
                            $fileName = 'AttachmentFactoryImage.gif';
                            break;
                        case 'image/jpeg':
                            $fileName = 'AttachmentFactoryImage.jpg';
                            break;
                        case 'application/pdf': //todo check mimetype with test pdf
                            $fileName = 'AttachmentFactoryPdf.pdf';
                            break;
                        case 'audio/mpeg': //todo check mimetype with test mp3
                            $fileName = 'AttachmentFactoryAudio.mp3';
                            $audioUploadOptions = array_shift($this->audioUploadOptions);
                            break;
                        default:
                            throw new \Exception('This file MimeType is not supported');
                    }

                    $upload->store('', 'attachments');
                    $uploadJson = $audioUploadOptions ?? [];
                    /*$this->audioUploadOptions[$upload->getClientOriginalName()] ?? []; */
                    $attachementRequest = new CreateAttachmentRequest([
                        "type"       => "file",
                        "title"      => $fileName, //was: $upload->getClientOriginalName(),
                        "json"       => json_encode($uploadJson),
                        "attachment" => $upload,
                    ]);

                    (new AttachmentsController)
                        ->store(
                            $this->lastTestQuestion,
                            $attachementRequest
                        );
                });
            }, User::find($this->testModel->author_id));
        }

        if (isset($this->videos)) {
            $this->doWhileLoggedIn(function () {
                $this->videos->each(function ($video) {
                    $request = new  CreateAttachmentRequest([
                        "type" => "video",
                        "link" => $video['link'],
                        'title' => 'Rickyboiii'
                    ]);
                    (new AttachmentsController)
                        ->store(
                            $this->lastTestQuestion,    //TestQuestion
                            $request                    //CreateAttachmentRequest
                        );
                });
            }, User::find($this->testModel->author_id));
        }
    }

    public function addVideoAttachment(string $link = 'https://www.youtube.com/watch?v=dQw4w9WgXcQ')
    {
        $this->videos->add([
            'link' => $link,
        ]);
        return $this;
    }

    public function addImageAttachment()
    {
        $originalFile = app_path('Factories/Attachments/image.png');

        return $this->addUploadAttachment($originalFile);
    }

    public function addPdfAttachment()
    {
        $originalFile = app_path('Factories/Attachments/file.pdf');

        return $this->addUploadAttachment($originalFile);
    }

    public function addAudioAttachment(bool $playOnce = false, bool $pausable = false, ?int $timeout = null)
    {
        $originalFile = app_path('Factories/Attachments/audio.mp3');

        $uploadOptions = [];
        if ($playOnce) {
            $uploadOptions = array_merge($uploadOptions, ['play_once' => (string)$playOnce]);
        }
        if ($pausable) {
            $uploadOptions = array_merge($uploadOptions, ['pausable' => (string)$pausable]);
        }
        if ($timeout) {
            $uploadOptions = array_merge($uploadOptions, ['timeout' => (string)$timeout]);
        }
        $this->audioUploadOptions[] = $uploadOptions;
        //settings are added on chronological order, handleAttachments can process them in the same order.

        return $this->addUploadAttachment($originalFile);
    }

    protected function addUploadAttachment(string $originalFile)
    {
        $extension = substr($originalFile, strpos($originalFile, '.'));

        $tmpFileName = Carbon::now()->getTimestamp() . $extension;

        copy($originalFile, storage_path('app/livewire-tmp/') . $tmpFileName);

        $upload = new TemporaryUploadedFile($tmpFileName, 'local');

        $this->uploads[] = $upload;

        return $this;
    }

    protected function definition()
    {
        return [
            "add_to_database"          => 1,
            "add_to_database_disabled" => 0,
            "bloom"                    => null,
            "closeable"                => 0,
            "decimal_score"            => 0,
            "discuss"                  => 1,
            "maintain_position"        => 0,
            "miller"                   => null,
            "is_open_source_content"   => 0,
            "tags"                     => [],
            "note_type"                => "NONE",
            "order"                    => 0,
            "rtti"                     => null,
            "score"                    => 5,
            "attainments"              => [],
            "test_id"                  => 0,
            "all_or_nothing"           => false,
            'answer'                   => $this->answerDefinition(),
            'question'                 => $this->questionDefinition(),
            "subtype"                  => $this->questionSubType(),
            "type"                     => $this->questionType(),
        ];
    }

    public function storeSubQuestions()
    {
        if (!$this->subQuestions) return;

        $this->doWhileLoggedIn(function () {
            $this->subQuestions->each(function($subQuestionFactory) {
                $factoryProperties = $subQuestionFactory->getQuestionProperties();
                GroupQuestionQuestion::store($this->lastTestQuestion, $factoryProperties);
            });
        }, User::find($this->testModel->author_id));
    }

    public function getQuestionProperties(): array
    {
        $this->questionProperties = array_merge($this->questionProperties, $this->calculatedQuestionProperties());

        return $this->questionProperties;
    }

    public function handleAfterStoreActions()
    {
        if($this->questionProperties['type'] === 'GroupQuestion') {
            $this->storeSubQuestions();
        }
    }

}
