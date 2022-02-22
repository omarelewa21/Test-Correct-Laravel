<?php


namespace tcCore\Http\Traits;

use tcCore\Question;


trait TestTakeNavigationForController
{

    private function getCloseableAudio($question){
        try {
            $closeableAudio = false;
            foreach ($question->attachments as $attachment) {
                if ($attachment->hasAudioTimeout()) {
                    $closeableAudio = true;
                    break;
                }
            }

            if (!is_null($question->belongs_to_groupquestion_id)) {
                $qroupQuestion = Question::findOrFail($question->belongs_to_groupquestion_id);
                foreach ($qroupQuestion->attachments as $attachment) {
                    if ($attachment->hasAudioTimeout()) {
                        $closeableAudio = true;
                        break;
                    }
                }
            }
            return $closeableAudio;
        }catch (\Exception $e){
            return false;
        }
    }
}