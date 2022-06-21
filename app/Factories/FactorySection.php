<?php

namespace tcCore\Factories;

use tcCore\Factories\Traits\RandomCharactersGeneratable;
use tcCore\SchoolLocation;
use tcCore\Section;
use tcCore\Subject;

class FactorySection
{
    use RandomCharactersGeneratable;

    public $section;
    public $schoolLocation;
    protected $name;
    protected $demo;

    public static function create(SchoolLocation $schoolLocation, string $sectionName = null, bool $demo = false)
    {
        $factory = new static;
        $factory->schoolLocation = $schoolLocation;
        $factory->demo = $demo;
        $factory->name = $sectionName ?? 'generated section ' . $factory->randomCharacters(4);

        $properties = $factory->definition();

        //todo attach to $schoolLocation
        $factory->section = Section::create($properties);
        $factory->section->schoolLocations()->attach($schoolLocation);

        return $factory;
    }

    public function addSharedSchoolLocation(SchoolLocation $schoolLocation)
    {
        $this->section->sharedSchoolLocations()->attach($schoolLocation);

        return $this;
    }

    public function addSubject($baseSubject, $subjectName, $subjectAbbreviation = null, $demo = false)
    {
        //attach is not possible, it is not a many-to-many relation, but one-to-many and many-to-one

        // section 1 can have 2 subjects that are both baseSubject 1, section_id + base_subject_id together are not unique
        //only subject_id is unique

//        $this->section->subjects()->attach($baseSubject, ['name' => $subjectName, 'abbreviation' => $subjectAbbreviation, 'demo' => false]);

        $Subject_fillable = ['name', 'abbreviation', 'section_id', 'base_subject_id', 'demo'];
        $subject = new Subject([
            'name' => $subjectName,
            'abbreviation' => $subjectAbbreviation,
            'base_subject_id' => $baseSubject->getKey(),
            'demo' => $demo,
        ]);

        $this->section->subjects()->save($subject);
        return $this;
    }

    protected function definition(): array
    {
        return [
            'name' => $this->name,
            'demo' => (int) $this->demo,
        ];
    }
}