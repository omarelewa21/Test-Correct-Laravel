<?php

namespace tcCore\Exports;

use DOMXPath;
use Maatwebsite\Excel\Concerns\FromCollection;
use tcCore\Attainment;
use tcCore\BaseSubject;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class AttainmentConflictExport implements FromCollection, WithHeadings
{
    protected $questions;
    protected $collection;
    protected $handled = [];
    protected $baseSubject = '';
    protected $educationLevel = '';
    protected $educationLevelYear = '';

    public function __construct($questions,$weight)
    {
        $lean = $weight=='lean';
        $superLean = $weight=='superLean';
        $this->questions = $questions;
        $this->collection = collect([]);
        $this->fillCollection($lean,$superLean);
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return $this->collection;
    }



    public function headings(): array
    {
        return [
            'vraag',
            'code',
            'subcode',
            'subsubcode',
            'base_subject',
            'education_level',
            'education_level_year'
        ];
    }

    public function fillCollection($lean,$superLean)
    {
        foreach ($this->questions as $question)
        {
            $questionTitle = $this->getQuestionTitle($question);
            $this->setBaseSubject($question);
            $this->setEducationLevel($question);
            $this->setEducationLevelYear($question);
            $this->collection->push([0 => $questionTitle, 1 => '', 2 => '', 3 => '', 4 => '', 5 => '', 6 => '']);
            $this->fillAttainmentRows($question->getQuestionInstance(),$lean,$superLean);
        }
    }

    public function fillAttainmentRows($question,$lean,$superLean)
    {
        $branches = $this->getAttainmentBranches($question);
        if(count($branches)==0 && ($lean || $superLean)){
            $this->collection->pop();
            return;
        }
        if(count($branches)==1 && $superLean){
            $this->collection->pop();
            return;
        }
        foreach ($branches as $branch){
            $this->collection->push($branch);
        }

    }

    protected function getAttainmentBranches($question)
    {
        $branches  = [];
        $this->handled = [];
        $questionAttainments = $question->attainments;
        $attainmentIds = $questionAttainments->pluck('id')->toArray();
        $subsubcodeAttainments = $questionAttainments->whereNotNull('subsubcode');
        foreach($subsubcodeAttainments as $subsubcodeAttainment){
            if($subsubcodeAttainment->subsubcode==''){
                continue;
            }
            $branches[] = $this->fillBranchFromSubsubcode($subsubcodeAttainment,$attainmentIds);
        }
        $subcodeAttainments = $questionAttainments->whereNotNull('subcode')->whereNull('subsubcode');
        foreach($subcodeAttainments as $subcodeAttainment){
            if($subcodeAttainment->subcode==''){
                continue;
            }
            if(in_array($subcodeAttainment->id,$this->handled)){
                continue;
            }
            $branches[] = $this->fillBranchFromSubcode($subcodeAttainment,$attainmentIds);
        }
        $codeAttainments = $questionAttainments->whereNotNull('code')->whereNull('subcode');
        foreach($codeAttainments as $codeAttainment){
            if(in_array($codeAttainment->id,$this->handled)){
                continue;
            }
            $branches[] = $this->fillBranchFromCode($codeAttainment,$attainmentIds);
        }
        return $branches;
    }

    protected function fillBranchFromSubsubcode($subsubcodeAttainment,$attainmentIds)
    {
        $subcodeAttainment = Attainment::find($subsubcodeAttainment->attainment_id);
        $codeAttainment = null;
        if(!is_null($subcodeAttainment)){
            $codeAttainment = Attainment::find($subcodeAttainment->attainment_id);
        }
        return $this->fillBranch($subsubcodeAttainment,$subcodeAttainment,$codeAttainment,$attainmentIds);
    }

    protected function fillBranchFromSubcode($subcodeAttainment,$attainmentIds)
    {
        $subsubcodeAttainment = null;
        $codeAttainment = null;
        if(!is_null($subcodeAttainment)){
            $codeAttainment = Attainment::find($subcodeAttainment->attainment_id);
        }
        return $this->fillBranch($subsubcodeAttainment,$subcodeAttainment,$codeAttainment,$attainmentIds);
    }

    protected function fillBranchFromCode($codeAttainment,$attainmentIds)
    {
        $subsubcodeAttainment = null;
        $subcodeAttainment = null;
        return $this->fillBranch($subsubcodeAttainment,$subcodeAttainment,$codeAttainment,$attainmentIds);
    }

    protected function fillBranch($subsubcodeAttainment,$subcodeAttainment,$codeAttainment,$attainmentIds)
    {
        $this->fillHandled([$subsubcodeAttainment,$subcodeAttainment,$codeAttainment]);
        return [    '',
                $this->getAttainmentLabel($codeAttainment,$attainmentIds),
                $this->getAttainmentLabel($subcodeAttainment,$attainmentIds),
                $this->getAttainmentLabel($subsubcodeAttainment,$attainmentIds),
                $this->baseSubject,
                $this->educationLevel,
                $this->educationLevelYear
        ];
    }


    protected function getAttainmentLabel($attainment,$attainmentIds)
    {
        return (is_null($attainment))?'':((!in_array($attainment->id,$attainmentIds))?'':   $attainment->description.'*****'.
                                                                                            $attainment->code.'-'.
                                                                                            $attainment->subcode.'-'.
                                                                                            $attainment->subsubcode.'***** id:'.
                                                                                            $attainment->id.'***** parent:'.
                                                                                            $attainment->attainment_id);
    }

    protected function fillHandled($arr)
    {
        foreach ($arr as $attainment){
            $this->handled[] = optional($attainment)->id;
        }
    }

    protected function getQuestionTitle($question)
    {
        try{
            $doc = new \DOMDocument('utf-8');
            $doc->loadHTML('<div>'.$question->getQuestionInstance()->getAttribute('question').'</div>');
            $xpath = new DOMXPath($doc);
            if($xpath->query('/html/body/div/*')->length>0){
                $txt = $doc->saveHTML($xpath->query('/html/body/div/*')->item(0));
            }else{
                $txt = utf8_encode(substr($question->getQuestionInstance()->getAttribute('question'),0,20));
            }
            if(strlen($txt)>40){
                $txt = substr($txt,0,40);
            }
            return $txt.'('.$question->getKey().')';
        }catch(\Exception $e){
            return utf8_encode(substr($question->getQuestionInstance()->getAttribute('question'),0,20)).'('.$question->getKey().')';
        }
    }

    protected function setBaseSubject($question)
    {
        $this->baseSubject = optional(optional($question->subject)->baseSubject)->name;
    }

    protected function setEducationLevel($question)
    {
        $this->educationLevel = optional($question->educationLevel)->name;
    }

    protected function setEducationLevelYear($question)
    {
        $this->educationLevelYear = $question->education_level_year;
    }

}
