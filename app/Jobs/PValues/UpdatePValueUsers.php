<?php

namespace tcCore\Jobs\PValues;

use tcCore\Jobs\Job;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;
use tcCore\PValue;

class UpdatePValueUsers extends Job implements SelfHandling, ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    protected $schoolClassId;
    protected $oldSchoolClassId;
    protected $subjectId;
    protected $oldSubjectId;
    protected $userId;
    protected $oldUserId;

    /**
     * Create a new job instance.
     *
     * @param $schoolClassId integer
     * @param $subjectId integer
     * @param $userId integer
     * @param $oldSchoolClassId integer
     * @param $oldSubjectId integer
     * @param $oldUserId integer
     */
    public function __construct($schoolClassId, $subjectId, $userId, $oldSchoolClassId, $oldSubjectId, $oldUserId)
    {
        $this->schoolClassId = $schoolClassId;
        $this->subjectId = $subjectId;
        $this->userId = $userId;
        $this->oldSchoolClassId = $oldSchoolClassId;
        $this->oldSubjectId = $oldSubjectId;
        $this->oldUserId = $oldUserId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->schoolClassId == $this->oldSchoolClassId && $this->subjectId == $this->oldSubjectId && $this->userId == $this->oldUserId) {
            return;
        }

        if ($this->oldSchoolClassId !== null && $this->oldSubjectId !== null && $this->oldUserId !== null) {
            $this->deattach($this->oldSchoolClassId, $this->oldSubjectId, $this->oldUserId);
        }

        if ($this->schoolClassId !== null && $this->subjectId !== null && $this->userId !== null) {
            $this->attach($this->schoolClassId, $this->subjectId, $this->userId);
        }
    }

    protected function deattach($oldSchoolClassId, $oldSubjectId, $oldUserId)
    {
        $pValues = PValue::where('school_class_id', $oldSchoolClassId)->where('subject_id', $oldSubjectId)->get();

        foreach ($pValues as $pValue) {
            $pValue->fill(['delete_user' => $oldUserId]);
            $pValue->save();
        }
    }

    protected function attach($schoolClassId, $subjectId, $userId)
    {
        $pValues = PValue::where('school_class_id', $schoolClassId)->where('subject_id', $subjectId)->get();

        foreach ($pValues as $pValue) {
            $pValue->fill(['add_user' => $userId]);
            $pValue->save();
        }
    }
}
