<?php
/**
 * Created by PhpStorm.
 * User: erik
 * Date: 09/04/2020
 * Time: 10:59
 */

namespace tcCore\Http\Helpers;


use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;
use tcCore\Answer;
use tcCore\AnswerParentQuestion;
use tcCore\AnswerRating;
use tcCore\Invigilator;
use tcCore\TestParticipant;
use tcCore\TestTake;
use tcCore\TestTakeEvent;

class TestTakeDuplicateHelper
{
    protected $data;
    protected $id;
    protected $testId;
    protected $teacherId;
    protected $studentIds;
    protected $schoolClassId;
    protected $periodId;
    protected $schoolLocationId;

    protected $new;

    public function collect($id, $testId,$teacherId, $studentIds, $schoolClassId, $periodId, $schoolLocationId)
    {
        $this->id = $id;
        $this->testId = $testId;
        $this->teacherId = $teacherId;
        $this->studentIds= collect($studentIds);
        $this->schoolClassId = $schoolClassId;
        $this->periodId = $periodId;
        $this->schoolLocationId = $schoolLocationId;

        $this->data = collect([]);
        $this->getTestTake();
        $this->getParticipantsIfAvailable();
        $this->getAnswersIfAvailable();
        $this->getAnswerParentQuestionsIfAvailable();
        $this->getAnswerRatingsIfAvailable();
        $this->getTestTakeEventsIfAvailable();

        return $this;
    }

    public function duplicate()
    {
        DB::beginTransaction();

        try {
            $this->startDuplication();
        } catch (\Exception $e) {
            DB::rollback();
//            logger('===== error ' . $e->getMessage());
            throw $e;
        }
        DB::commit();
        return $this;
    }

    public function getData()
    {
        return $this->data;
    }

    public function getNew()
    {
        return $this->new;
    }

    protected function startDuplication()
    {
        $this->new = collect([]);
        $this->duplicateTestTake();
        $this->duplicateParticipantsWithNewIds();
        $this->duplicateAnswers();
        $this->duplicateAnswerParentQuestions();
        $this->duplicateAnswerRatings();
        $this->duplicateTestTakeEvents();
    }

    protected function duplicateTestTake()
    {
        $testTake = $this->data['testTake']->cloneModelOnly();
        $testTake->user_id = $this->teacherId;
        $testTake->period_id = $this->periodId;
        $testTake->test_id = $this->testId;
        $testTake->school_location_id = $this->schoolLocationId;
        $testTake->demo = 1;
        if($testTake->discussed_user_id !== null){
            $testTake->discussed_user_id = $this->teacherId;
        }

        if (!$testTake->save()) {
            throw new \Exception('could not save the duplicated test take');
        }

        Invigilator::create([
           'test_take_id' => $testTake->getKey(),
           'user_id' => $this->teacherId,
        ]);

        $this->new->put('testTake',$testTake);
    }

    protected function duplicateParticipantsWithNewIds()
    {
        $ref = (object) ['newParticipants' => [], 'refIds' => [], 'refUserIds' => []];
        $this->new->put('participants',$this->data['participants']->each(function(TestParticipant $tp, $key) use ($ref) {
            $ref->refUserIds[$tp->user_id] = $this->studentIds[$key];
            $newTp = $tp->cloneModelOnly();
            $newTp->fill([
                'user_id' => $this->studentIds[$key],
                'test_take_id' => $this->new['testTake']->getKey(),
                'school_class_id' => $this->schoolClassId,
            ]);

            if(!$newTp->save()){
                throw new \Exception('could not save the duplicated participant');
            }
            // records need to be deleted as they are created on save for the testparticipant
            Answer::where('test_participant_id',$newTp->getKey())->forceDelete();
            TestTakeEvent::where('test_participant_id',$newTp->getKey())->forceDelete();

            $ref->refIds[$tp->getKey()] = $newTp->getKey();
            return $newTp;
        }));
        $this->new->put('refUserIds',$ref->refUserIds);
        $this->new->put('refParticipantIds',$ref->refIds);
    }

    protected function duplicateAnswers()
    {
        if(isset($this->data['answers'])) {
            $ref = (object)['answerIds' => []];
            $this->new->put('answers', $this->data['answers']->map(function (Answer $a) use ($ref) {
                $new = $a->cloneModelOnly();
                $new->fill([
                    'test_participant_id' => $this->new['refParticipantIds'][$a->test_participant_id],
                ]);

                $new->setAttribute('uuid', Uuid::uuid4());

                if (!$new->save()) {
                    throw new \Exception('could not save the duplicated Answers');
                }
                $ref->answerIds[$a->getKey()] = $new->getKey();
                return $new;
            }));

            $this->new->put('refAnswerIds', $ref->answerIds);
            collect($this->data['participants'])->transform(function (TestParticipant $p) use ($ref) {
                if ($p->anser_id !== null) {
                    $p->answer_id = $ref->answerIds[$p->answer_id];
                    $p->save();
                }
            });
        }
    }

    protected function duplicateAnswerParentQuestions()
    {
        if(isset($this->data['answerParentQuestions'])) {
            $this->new->put('answerParentQuestions', $this->data['answerParentQuestions']->map(function (AnswerParentQuestion $a) {
                $new = $a->cloneModelOnly();
                $new->fill([
                    'answer_id' => $this->new['refAnswerIds'][$a->answer_id],
                ]);

                $new->setAttribute('uuid', Uuid::uuid4());

                if (!$new->save()) {
                    throw new \Exception('could not save the duplicated answer parent question');
                }
                return $new;
            }));
        }
    }

    protected function duplicateAnswerRatings()
    {
        if(isset($this->data['answerRatings'])) {
            $this->new->put('answerRatings', $this->data['answerRatings']->map(function (AnswerRating $r) {
                $new = $r->cloneModelOnly();
                if ($new->type === 'STUDENT') {
                    $userId = ($r->user_id !== null) ? $this->new['refUserIds'][$r->user_id] : null;
                } else if ($new->type === 'TEACHER') {
                    $userId = $this->teacherId;
                } else {
                    $userId = null;
                }
                $new->fill([
                    'answer_id' => $this->new['refAnswerIds'][$r->answer_id],
                    'user_id' => $userId,
                    'test_take_id' => $this->new['testTake']->getKey(),
                ]);

                if (!$new->save()) {
                    throw new \Exception('could not save the duplicated answerrating');
                }
                return $new;
            }));
        }
    }

    protected function duplicateTestTakeEvents()
    {
        $this->new->put('testTakeEvents',$this->data['testTakeEvents']->map(function(TestTakeEvent $t){
            $new = $t->cloneModelOnly();
            $new->fill([
               'test_participant_id' => $this->new['refParticipantIds'][$t->test_participant_id],
            ]);
            $new->test_take_id = $this->new['testTake']->getKey();
            if(!$new->save()){
                throw new \Exception('could not save the duplicated test take events');
            }
            return $new;
        }));
    }

    protected function getTestTake()
    {
        $this->data->put('testTake',TestTake::findOrFail($this->id));
    }

    /**
     * Get max the number of participants as there are students
     */
    protected function getParticipantsIfAvailable()
    {
        $this->data->put('participants',TestParticipant::where('test_take_id',$this->id)->skip(0)->take($this->studentIds->count())->get());
    }

    protected function getAnswersIfAvailable()
    {
        if($this->data['participants']->count()) {
            $this->data->put('answers', Answer::whereIn('test_participant_id', $this->data['participants']->select('id'))->get());
        }
    }

    protected function getAnswerParentQuestionsIfAvailable()
    {
        if($this->data['answers']->count()){
            $this->data->put('answerParentQuestions',AnswerParentQuestion::whereIn('answer_id', $this->data['answers']->select('id'))->get());
        }
    }

    protected function getAnswerRatingsIfAvailable()
    {
        if($this->data['participants']->count()) {
            $this->data->put('answerRatings', AnswerRating::whereIn('answer_id', $this->data['answers']->select('id'))->get());
        }
    }

    protected function getTestTakeEventsIfAvailable()
    {
        if($this->data['participants']->count()) {
            $this->data->put('testTakeEvents', TestTakeEvent::whereIn('test_participant_id', $this->data['participants']->select('id'))->get());
        }
    }

}
