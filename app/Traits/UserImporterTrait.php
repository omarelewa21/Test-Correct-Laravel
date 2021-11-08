<?php

namespace tcCore\Traits;

use tcCore\Lib\Repositories\SchoolYearRepository;
use tcCore\SchoolClass;
use tcCore\Subject;

trait UserImporterTrait {
    private function usernameExternalIdSchoolClassSubjectUniqueInImport(&$validator)
    {
        $dataCollection = collect(request('data'));
        $essentialDataCollection = $dataCollection->map(function ($item, $key) {
            $username = array_key_exists('username',$item)?$item['username']:'';
            $external_id = array_key_exists('external_id',$item)?$item['external_id']:'';
            $school_class = array_key_exists('school_class',$item)?$item['school_class']:'';
            $subject = array_key_exists('subject',$item)?$item['subject']:'';
            return [    'username' => $username,
                'external_id' => $external_id,
                'school_class' => $school_class,
                'subject' => $subject,
            ];
        });
        $unique = $essentialDataCollection->unique();
        if ($unique->count() < $essentialDataCollection->count()) {
            $duplicates = $essentialDataCollection->keys()->diff($unique->keys());
            $duplicates->each(function($duplicate) use ($validator) {
                $validator->errors()->add(
                    sprintf('data.%d.duplicate', $duplicate), 'Dit record komt meerdere keren voor;'
                );
            });
        }
    }

    private function usernameExternalIdCombinationUnique(&$validator)
    {
        $dataCollection = collect(request('data'));
        $usernameDataCollection = $dataCollection->pluck('username');
        $externalIdDataCollection = $dataCollection->pluck(['external_id']);
        $usernameExternalIdDataCollection = $dataCollection->map(function ($item, $key) {
            $username = array_key_exists('username',$item)?$item['username']:'';
            $external_id = array_key_exists('external_id',$item)?$item['external_id']:'';
            return [    'username' => $username,
                'external_id' => $external_id,
            ];
        });
        $this->validateUsernameExternalIdCombination($validator,$usernameDataCollection,$usernameExternalIdDataCollection,'username','external_id');
        $this->validateUsernameExternalIdCombination($validator,$externalIdDataCollection,$usernameExternalIdDataCollection,'external_id','username');

    }

    private function getSchoolClassByName($school_class_name) {
        return SchoolClass::filtered()->orderBy('created_at', 'desc')->get()->filter(function ($school_class) use ($school_class_name) {
            return strtolower($school_class_name) === strtolower($school_class->name);
        })->first();
    }

    private function getSubjectByName($subject_name) {
        return Subject::filtered()->get()->filter(function ($subject) use ($subject_name) {
            return strtolower($subject_name) === strtolower($subject->name);
        })->first();
    }

    private function schoolClassYearIsActual($schoolClass){
        $currentYear = SchoolYearRepository::getCurrentSchoolYear();
        return (null !== $currentYear && $currentYear->getKey() === $schoolClass->schoolYear->getKey());
    }

    private function validateUsernameExternalIdCombination(&$validator,$paramCollection,$usernameExternalIdDataCollection,$primaryParamName,$secondaryParamName)
    {
        $unique = $paramCollection->unique();
        $duplicates = $paramCollection->diffAssoc($unique);
        $duplicates->each(function($duplicate,$duplicatekey) use ($validator,$usernameExternalIdDataCollection,$primaryParamName,$secondaryParamName) {
            if(empty($duplicate)){
                return true;
            }
            $firstEntry = $usernameExternalIdDataCollection->where($primaryParamName,$duplicate)->first();
            $secondaryParam = $firstEntry[$secondaryParamName];
            $filtered = $usernameExternalIdDataCollection->filter(function ($value, $key) use ($duplicate,$primaryParamName) {
                return $value[$primaryParamName] == $duplicate;
            });
            foreach ($filtered as $entry){
                if($entry[$secondaryParamName]!=$secondaryParam){

                    $validator->errors()->add(
                        sprintf('data.%d.duplicate', $duplicatekey), sprintf('this record occurs multiple times with %s combination',$secondaryParamName)
                    );
                }

            }
        });
    }

    protected function hasEntry($key,$arr)
    {
        if(!array_key_exists($key,$arr)){
            return false;
        }
        if(is_null($arr[$key])){
            return false;
        }
        if($arr[$key]==''){
            return false;
        }
        return true;
    }
}