<?php

namespace tcCore\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;
use Ramsey\Uuid\Uuid;
use tcCore\Answer;
use tcCore\AnswerParentQuestion;
use tcCore\Attainment;
use tcCore\CitoExportRow;
use tcCore\CompletionQuestion;
use tcCore\CompletionQuestionAnswerLink;
use tcCore\Exports\CitoExport;
use tcCore\Http\Controllers\TestQuestionsController;
use tcCore\Http\Helpers\AnswerParentQuestionsHelper;
use tcCore\Http\Helpers\CompletionQuestionAnswerHelper;
use tcCore\Http\Helpers\TestTakeRecalculationHelper;
use tcCore\Lib\Question\QuestionGatherer;
use tcCore\Log;
use tcCore\MatrixQuestionAnswer;
use tcCore\MatrixQuestionAnswerSubQuestion;
use tcCore\MatrixQuestionSubQuestion;
use tcCore\MultipleChoiceQuestionAnswer;
use tcCore\Question;
use tcCore\QuestionAttainment;
use tcCore\RankingQuestionAnswer;
use tcCore\TestParticipant;
use tcCore\TestQuestion;
use tcCore\TestTake;

class CreateCitoExport extends Command
{

    use BaseCommandTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cito:export';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'create the cito exports in several steps';

    protected $stepOptions = [
        '1' => 'collecting all answers records and set answer number',
        '2' => 'set all values',
        '3' => 'add all test take and user id\'s',
        '4' => 'collect all questions, subjects and leerdoelen',
        '5' => 'collect all scores',
        '6' => 'add brin numbers',
        '7' => 'merge data into 1 record per test participant',
        '8' => 'export to disk',
    ];

    protected $bar = null;
    protected $stepNr = 0;

    protected $noAnswerVal = '--GeenAntwoord';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    protected function getStepNr($stepText)
    {
        foreach($this->stepOptions as $key => $val){
            if($stepText === $val){
                return $key;
            }
        }

        return 0;
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        ini_set('memory_limit', '-1');

        $stepNr = array_search(
            $this->choice('Where do you want to start?', $this->stepOptions),
            $this->stepOptions
        );

        $methodName = $this->getMethodNameOrFail($stepNr);
        $this->stepNr = $stepNr;
        $this->$methodName();
    }

    // set all brin numbers
    protected function handleStep8()
    {

        $this->handleStartOfStep();
        $fileName = sprintf('cito-export-tlc-%s.xlsx',date('Ymd'));
        $file = storage_path($fileName);
        if (file_exists($file)) {
            unlink($file);
        }
        Excel::store(new CitoExport(),$fileName);
        $this->handleEndOfStep();
    }

    // merge data into 1 record per test participant
    protected function handleStep7()
    {
        $testParticipantCount = CitoExportRow::distinct()->pluck('test_participant_id')->count();
        $citoRows = CitoExportRow::orderBy('test_participant_id')->orderBy('number')->get();
        $this->handleStartOfStep($testParticipantCount);
        $this->line(PHP_EOL);
        $this->line('');

        $data = ['export' => true];
        $mergeRow = null;

        $citoRows->each(function(CitoExportRow $c) use (&$data, &$mergeRow){
            if($mergeRow == null){
                $mergeRow = $c;
            } else if($mergeRow->test_participant_id !== $c->test_participant_id){
                // merge all
                $mergeRow->update($data);
                $this->bar->advance();
                $mergeRow = $c;
                $data = ['export' => true];
            } else {
                // collect data
                $fieldNumber = $this->getFieldNumber($c);
                $answerField = sprintf('answer_%d',$fieldNumber);
                $scoreField = sprintf('score_%d',$fieldNumber);
                $itemField = sprintf('item_%d',$fieldNumber);
                $data[$answerField] = $c->$answerField;
                $data[$scoreField] = $c->$scoreField;
                $data[$itemField] = $c->$itemField;
            }
        });
        if(count($data) > 0 && null !== $mergeRow){
            $mergeRow->update($data);
        }

        $this->handleEndOfStep();
    }

    // set all brin numbers
    // instead of brin nummers we use the customer_code as brin isn't set mostly
    protected function handleStep6()
    {
        $citoRows = CitoExportRow::all();
        $this->handleStartOfStep($citoRows->count());

        $citoRows->each(function(CitoExportRow $c){
            $brin = \DB::table('users')->where('users.id',$c->user_id)
                ->join('school_locations','users.school_location_id','=','school_locations.id')
                ->value('customer_code');

            $c->update(['brin' => $brin]);
            $this->bar->advance();
        });

        $this->handleEndOfStep();
    }

    // set all scores
    protected function handleStep5()
    {
        $citoRows = CitoExportRow::all();
        $this->handleStartOfStep($citoRows->count());

        $citoRows->each(function(CitoExportRow $c){
            $fieldNumber = $this->getFieldNumber($c);
            $answerField = sprintf('answer_%d',$fieldNumber);
            if($c->$answerField == $this->noAnswerVal){
                $rating = -1;
            } else {
                $rating = \DB::table('answers')->where('answers.id', $c->answer_id)->join('answer_ratings', function ($join) {
                    $join->on('answers.id', '=', 'answer_ratings.answer_id')
                        ->where('type', 'SYSTEM');
                })->value('rating');
            }
            $fieldName = sprintf('score_%d',$fieldNumber);
            $c->update([$fieldName => $rating]);
            $this->bar->advance();
        });

        $this->handleEndOfStep();
    }

    // collect all questions, subjects and leerdoelen
    protected function handleStep4()
    {
        $citoRows = CitoExportRow::all();
        $this->handleStartOfStep($citoRows->count());

        // instead of the attainments we use the title of the test
        $citoRows->each(function(CitoExportRow $c){
            $answer = Answer::where('id',$c->answer_id)->with(['testParticipant','testParticipant.testTake','testParticipant.testTake.test','question','question.subject','question.questionAttainments'])->first();
            $fieldNumber = $this->getFieldNumber($c);
            $itemName = sprintf('item_%d',$fieldNumber);
             $c->update([
                $itemName => $answer->question->external_id,
                'vak' => $answer->question->subject->name,
                'leerdoel' => $answer->testParticipant->testTake->test->name,
            ]);
//        $attainments = Attainment::all();
//
//        $citoRows->each(function(CitoExportRow $c) use ($attainments){
//            $answer = Answer::where('id',$c->answer_id)->with(['testParticipant','testParticipant.testTake','testParticipant.testTake.test','question','question.subject','question.questionAttainments'])->first();
//            $fieldNumber = $this->getFieldNumber($c);
//            $itemName = sprintf('item_%d',$fieldNumber);
//            $_attainments = [];
//            $answer->question->questionAttainments->each(function(QuestionAttainment $qa) use (&$_attainments, $attainments){
//                $attainment = $attainments->firstWhere('id',$qa->attainment_id);
//               $attainments[] =  sprintf('%s%s %s',$attainment->code, $attainment->sub_code,$attainment->description);
//            });
//            $c->update([
//                $itemName => $answer->question->external_id,
//                'vak' => $answer->question->subject->name,
//                'leerdoel' => implode(', ',$_attainments),
//            ]);
            $this->bar->advance();
        });

        $this->handleEndOfStep();
    }

    // set all test take id and user id's
    protected function handleStep3()
    {
        $citoRows = CitoExportRow::all();
        $this->handleStartOfStep($citoRows->count());

        $citoRows->each(function(CitoExportRow $c){
            $answer = Answer::where('id',$c->answer_id)->with(['testParticipant'])->first();
            $c->update([
                'test_take_id' => $answer->testParticipant->test_take_id,
                'user_id' => $answer->testParticipant->user_id
            ]);
            $this->bar->advance();
        });

        $this->handleEndOfStep();
    }

    // set all values
    protected function handleStep2()
    {
        $citoRows = CitoExportRow::all();
        $this->handleStartOfStep($citoRows->count());


        $citoRows->each(function(CitoExportRow $c){
            $answer = Answer::find($c->answer_id);
            $question = $answer->question;
            $json = json_decode($answer->json);
            $answerValue = $this->noAnswerVal;
            $answerAr = [];

            switch($question->type){
                case 'RankingQuestion':
                    foreach((array) $json as $key => $value){
                        if(strlen(trim($value)) > 0){
                            $answerAr[] = sprintf('%s => %s',
                                $value,
                                RankingQuestionAnswer::find($key)->value('answer')
                            );
                        }
                    }
                    if(count($answerAr)){
                        $answerValue = implode(', ', $answerAr);
                    }
                    break;
                case 'MatrixQuestion':
                    foreach ((array)$json as $key => $value) {
                        $subQuestion = optional(MatrixQuestionSubQuestion::find($key))->value('sub_question');
                        $answer = optional(MatrixQuestionAnswer::find($value))->value('answer');
                        if($subQuestion && $answer) {
                            if(strlen(trim($answer)) > 0) {
                                $answerAr[] = sprintf('%s => %s',
                                    $subQuestion,
                                    $answer
                                );
                            }
                        }
                    }
                    if(count($answerAr)) {
                        $answerValue = implode(', ', $answerAr);
                    }
                    break;
                case 'CompletionQuestion':
                    foreach((array) $json as $key => $value){
                        if(strlen(trim($value)) > 0){
                            $answerAr[] = sprintf('%s => %s', $key, $value);
                        }
                    }
                    if(count($answerAr)){
                        $answerValue = implode(', ', $answerAr);
                    }
                    break;
                case 'MultipleChoiceQuestion':
                    if(null !== $question->multipleChoiceQuestionAnswers) {
                        $question->multipleChoiceQuestionAnswers->each(function (MultipleChoiceQuestionAnswer $a) use (&$answerAr, $json) {
                            foreach ((array)$json as $key => $value) {
                                if ((int)$key === $a->getKey()) {
                                    $answerAr[] = sprintf('%s => %s', $a->answer, $value);
                                }
                            }
                        });
                        if(count($answerAr)){
                            $answerValue = implode(', ', $answerAr);
                        }
                    }
                    break;
                case 'OpenQuestion':
                    $answerValue = $json->value;
                    break;
                default:
                    $this->line('');
                    $this->error($question->type.' not found');
                    $this->error($c->json);
                    $this->line('');
                    $this->line('');
            }

            $fieldNumber = $this->getFieldNumber($c);
            $fieldName = sprintf('answer_%d',$fieldNumber);
            $c->update([
               $fieldName =>  $answerValue,
               'question_type' => $question->type
            ]);

            $this->bar->advance();
        });

        $this->handleEndOfStep();
    }

    protected function handleStep1()
    {
        $answers = Answer::whereIn('question_id',Question::where('scope','cito')->pluck('id'))->whereNotNull('json')->get();

        $this->handleStartOfStep($answers->count());

        $answers->each(function(Answer $a){
            CitoExportRow::create([
                'test_participant_id' => $a->test_participant_id,
                'answer_id' => $a->getKey(),
                'json' => $a->json,
                'answered_at' => $a->created_at,
                'question_id' => $a->question_id,
                'number' => $a->order,
            ]);
            $this->bar->advance();
        });

        $this->handleEndOfStep();

    }

    protected function getFieldNumber(CitoExportRow $c){
        return ($c->number <= 16) ? $c->number : 16;
    }

    protected function handleStartOfStep($count = null)
    {
        $this->bar = null;
//        $this->line(sprintf('Step %d %s',$this->stepNr,$this->stepOptions[$this->stepNr]));
        if($count !== null){
            $this->bar = $this->output->createProgressBar($count);
        }
    }

    protected function handleEndOfStep()
    {
        if($this->bar !== null){
            $this->bar->finish();
        }
        $this->line('');
        $this->line('Step '.$this->stepNr.' done');
        $this->line('');
        $this->line('');

        $this->stepNr++;
        $this->askStartNextStep($this->stepNr);
    }

    public function askStartNextStep($newNr)
    {
        if(!array_key_exists($newNr,$this->stepOptions)){
            $this->line('No more steps to take, you\'re done, all should be in the database');
            exit;
        }

        if($newNr === 1){
            $question = sprintf('Hi, do you want to start with %s?',$this->stepOptions[$newNr]);
        } else {
            $question = sprintf('Do you want to %s?',$this->stepOptions[$newNr]);
        }

        if($this->confirm($question,true)){
            $methodName = $this->getMethodNameOrFail($newNr);
            $this->info(sprintf('Going to start with step %d: `%s`',$newNr,$this->stepOptions[$newNr]));
            $this->stepNr = $newNr;
            $this->$methodName();
        } else {
            $this->line('');
            $this->line('Ok, were done');
        }
    }

    protected function getMethodNameOrFail($newNr)
    {
        $methodName = 'handleStep'.$newNr;
        if(!method_exists($this,$methodName)){
            $this->error('Could not find the method `'.$methodName.'` so next step failed');
            exit;
        }
        return $methodName;
    }

}
