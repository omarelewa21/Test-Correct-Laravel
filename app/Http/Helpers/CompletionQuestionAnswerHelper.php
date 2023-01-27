<?php
/**
 * Created by PhpStorm.
 * User: erik
 * Date: 09/04/2020
 * Time: 10:59
 */

namespace tcCore\Http\Helpers;


use Illuminate\Support\Facades\DB;
use tcCore\Answer;
use tcCore\AnswerParentQuestion;
use tcCore\CompletionQuestion;
use tcCore\CompletionQuestionAnswerLink;
use tcCore\Lib\Question\QuestionGatherer;
use tcCore\Question;
use tcCore\School;
use tcCore\SchoolLocation;
use tcCore\TestParticipant;
use tcCore\TestTake;
use tcCore\UmbrellaOrganization;
use tcCore\User;

class CompletionQuestionAnswerHelper
{

    protected $commandEnv = false;
    protected $dryRun = true;
    protected $resultset = [];
    protected $correctNrs = [];

    protected function hasCommandEnv()
    {
        return $this->commandEnv !== null;
    }

    public function __construct($commandEnv = null, $dryRun = true)
    {
        $this->commandEnv = $commandEnv;
        $this->dryRun = $dryRun;
    }

    public function fixQuestionAnswerLinks(CompletionQuestion $question)
    {
        $activeAnswerLinks = $question->completionQuestionAnswerLinks;
        $deletedAnswerLinks = $question->completionQuestionAnswerLinks()->onlyTrashed()->get();

        if(null !== $activeAnswerLinks && null !== $deletedAnswerLinks && $deletedAnswerLinks->count() > 0) {
            $restoreAnswerLinks = $this->getRestoreAnswerLinks($activeAnswerLinks, $deletedAnswerLinks, $question);

            return $this->handleFoundAnswerLinks($restoreAnswerLinks, $activeAnswerLinks,$question);
        } else {
            if($this->hasCommandEnv()){
                $this->commandEnv->toComment(sprintf('nothing to restore (active: %d, deleted: %d)...is that correct???', (null !== $activeAnswerLinks) ? $activeAnswerLinks->count() : 0,$deletedAnswerLinks->count()));
            }
            return false;
        }
        return false;
    }

    protected function handleFoundAnswerLinks($restoreAnswerLinks, $activeAnswerLinks, CompletionQuestion $question)
    {
        if (null !== $restoreAnswerLinks && $activeAnswerLinks->count() === $restoreAnswerLinks->count() ) {
            if($activeAnswerLinks->first()->completion_question_answer_id < $restoreAnswerLinks->first()->completion_question_answer_id){
                if($this->hasCommandEnv()){
                    $this->commandEnv->toError(sprintf('to be restored records are newer (%s) than the active ones (%s)', $restoreAnswerLinks->first()->created_at, $activeAnswerLinks->first()->created_at));
                }
                return false;
            }

            // restoreAnswerLinks need to be undeleted and activeAnswerLinks need to be force deleted
            return $this->restoreDeleteRecords($restoreAnswerLinks, $activeAnswerLinks);

        } else {
            // throw error cause this is strange, something is wrong with this one and needs attention
            throw new \Exception(
                sprintf(
                    'QUESTION #%d (%s) Something weird happend. The to be restored answer links (%d) are not in line with the active answer links (%d)',
                    $question->id,
                    $question->getQuestionInstance()->external_id,
                    $restoreAnswerLinks->count(),
                    $activeAnswerLinks->count()
                )
            );
        }
    }

    protected function restoreDeleteRecords($toBeRestored, $toBeDeleted)
    {

        $tagNr = null;
        $nr = 1;
        $toBeRestored->each(function (CompletionQuestionAnswerLink $l) use (&$tagNr, &$nr) {
            if($tagNr == null || $tagNr != $l->completionQuestionAnswer->tag){
                $nr = 1;
                $tagNr = $l->completionQuestionAnswer->tag;
            }
            if((bool) $l->completionQuestionAnswer->correct === true){
                $this->correctNrs['new'][] = $nr;
            }
            if($this->dryRun === false) {
                $l->restore();
            }
            $nr++;
        });

        $tagNr = null;
        $nr = 1;
        $toBeDeleted->each(function (CompletionQuestionAnswerLink $l)  use (&$tagNr, &$nr) {
            if($tagNr == null || $tagNr != $l->completionQuestionAnswer->tag){
                $nr = 1;
                $tagNr = $l->completionQuestionAnswer->tag;
            }
            if((bool) $l->completionQuestionAnswer->correct === true){
                $this->correctNrs['old'][] = $nr;
            }
            if($this->dryRun === false) {
                $l->delete();
            }
            $nr++;
        });

        if ($this->hasCommandEnv()) {
            $this->commandEnv->toInfo(sprintf('%srestored: %d, force deleted: %d', $this->dryRun ? 'DRYRUN ' : '', $toBeRestored->count(), $toBeDeleted->count()));
            $this->commandEnv->toInfo(sprintf('old: %s, new %s', implode(', ',$this->correctNrs['old']), implode(', ',$this->correctNrs['new'])));
        }
        return true;
    }

    protected function getRestoreAnswerLinks($activeAnswerLinks, $deletedAnswerLinks, CompletionQuestion $question)
    {
        if($deletedAnswerLinks->count() !== $activeAnswerLinks->count()) {
            if ($this->hasCommandEnv()) {
                $this->commandEnv->toError(sprintf('there have been multiple deletes (%d), we take the first %d records as being correct',$deletedAnswerLinks->count(), $activeAnswerLinks->count()));
            }
            $restoreAnswerLinks = $question
                ->completionQuestionAnswerLinks()
                ->onlyTrashed()
                ->orderBy('completion_question_answer_id','asc')
//                ->orderBy('created_at', 'asc')
                ->orderBy('order', 'asc')
                ->take($activeAnswerLinks->count())
                ->get();
        } else {
            $restoreAnswerLinks = $deletedAnswerLinks;
//            $creationDates = $activeAnswerLinks->pluck('created_at')->unique();
//            $restoreAnswerLinks = $deletedAnswerLinks->filter(function (CompletionQuestionAnswerLink $link) use ($creationDates) {
//                return $creationDates->contains($link->deleted_at);
//            });
//            if (0 === $restoreAnswerLinks->count()) {
//                // can't merge these contains as there might be conflicts
//                $restoreAnswerLinks = $deletedAnswerLinks->filter(function (CompletionQuestionAnswerLink $link) use ($creationDates) {
//                    return $creationDates->contains($link->deleted_at->addSeconds(1)); // might not have been in the same second
//                });
//            }
//
//            if ($activeAnswerLinks->count() !== $restoreAnswerLinks->count()) {
//                // we might have deletion stamps that cross a second line so we need to check both
//                $restoreAnswerLinks = $deletedAnswerLinks->filter(function (CompletionQuestionAnswerLink $link) use ($creationDates) {
//                    return $creationDates->contains($link->deleted_at) || $creationDates->contains($link->deleted_at->addSeconds(1));
//                });
//            }
        }
        return $restoreAnswerLinks;
    }

    public function fixQuestions() {

        DB::beginTransaction();
        try {

            $questionIds = Question::whereScope('cito')->whereType('CompletionQuestion')->pluck('id');
            $completionQuestions = CompletionQuestion::whereSubtype('multi')->whereIn('id', $questionIds)->get();
            $fixed = 0;
            $completionQuestions->each(function (CompletionQuestion $q) use (&$fixed) {
                $this->correctNrs = [];
                if ($this->hasCommandEnv()) {
                    $this->commandEnv->toOutput(sprintf('<info>  o Question (%d): %s...</info>', $q->getKey(), $q->getQuestionInstance()->external_id), false);
                }

                if ($this->fixQuestionAnswerLinks($q)) {
                    $fixed++;
                }


            });
        } catch (\Exception $e) {
            DB::rollback();

            throw $e;
        }

        DB::commit();

        return [
            'total' => $completionQuestions->count(),
            'fixed' => $fixed
            ];
    }


}