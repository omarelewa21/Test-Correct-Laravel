<?php

namespace tcCore\Factories\Questions;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Livewire\TemporaryUploadedFile;
use tcCore\Attainment;
use tcCore\Factories\Interfaces\FactoryQuestion as FactoryQuestionInterface;
use tcCore\Http\Controllers\TestQuestions\AttachmentsController;
use tcCore\Http\Requests\CreateAttachmentRequest;
use tcCore\Subject;
use tcCore\Test;
use tcCore\TestQuestion;
use tcCore\User;
use tcCore\View\Components\Layouts\App;

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
            $this->questionProperties = array_merge($this->questionProperties, [
                'attainments' => Attainment::where(
                    'base_subject_id',
                    $this->testModel->subject->base_subject_id
                )->pluck('id')->random(2)->toArray()
            ]);
        }
        return $this;
    }

    public function addRandomTaxonomy($rtti = true, $miller = true, $bloom = true)
    {
        $rttiOptions = collect([
            'R',
            'T1',
            'T2',
            'I'
        ]);
        $bloomOptions = collect([
            "Onthouden",
            "Begrijpen",
            "Toepassen",
            "Analyseren",
            "Evalueren",
            "Creëren",
        ]);
        $millerOptions = collect([
            "Weten",
            "Weten hoe",
            "Laten zien",
            "Doen",
        ]);
        $taxonomy = [];
        if ($this->questionProperties['rtti'] == "" && $rtti) {
            $taxonomy['rtti'] = $rttiOptions->random();
        }
        if ($this->questionProperties['miller'] == "" && $miller) {
            $taxonomy['miller'] = $millerOptions->random();
        }
        if ($this->questionProperties['bloom'] == "" && $bloom) {
            $taxonomy['bloom'] = $bloomOptions->random();
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
            $this->calculatedQuestionProperties(),
        );

        //store when all properties and answers are added
        $this->lastTestQuestion = $this->doWhileLoggedIn(function () {
            return TestQuestion::store($this->questionProperties);
        }, User::find($this->testModel->author_id));
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
}