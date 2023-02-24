<?php

namespace Tests\Unit\FactoryTests\SchoolFactory;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use tcCore\BaseSubject;
use tcCore\Factories\FactoryBaseSubject;
use tcCore\Factories\FactorySchool;
use tcCore\Factories\FactorySchoolLocation;
use tcCore\Factories\FactorySection;
use tcCore\SchoolLocation;
use tcCore\Section;
use Tests\TestCase;

class FactorySectionTest extends TestCase
{
    use WithFaker;

    //BaseSubjects table should only be used as a lookUp table

    /**
     * Section always belongs to precisely one school_location
     * @test
     */
    public function can_create_Section_for_a_School_location()
    {
        $schoolLocation = FactorySchoolLocation::create(FactorySchool::create()->school)->schoolLocation;

        $section = FactorySection::create($schoolLocation, 'Arabisch')->section;

        $this->assertTrue($section->exists());
        $this->assertInstanceOf('tcCore\Section', $section);
        $this->assertInstanceOf('tcCore\SchoolLocation', $section->schoolLocations->first());
    }

    /** @test */
    public function can_make_section_shared_with_other_school_location()
    {
        $startCount = SchoolLocation::count();

        $school = FactorySchool::create('SectionOwner')->school;
        $schoolLocationFirst = FactorySchoolLocation::create($school)->schoolLocation;
        $schoolLocationSecond = FactorySchoolLocation::create($school)->schoolLocation;

        $factorySection = FactorySection::create($schoolLocationFirst);
        $this->assertEquals($startCount+2, SchoolLocation::count());

        $factorySection->addSharedSchoolLocation($schoolLocationSecond);

        //assert that the first schoolLocation can be accessed through the many-to-many relationships of section
        $this->assertEquals($schoolLocationFirst->name, $schoolLocationSecond->sharedSections->first()->schoolLocations->first()->name);
        //assert that the section has more than zero sharedSchoolLocations and schoolLocations
        $this->assertGreaterThan(0, $factorySection->section->fresh()->sharedSchoolLocations()->count());
        $this->assertGreaterThan(0, $factorySection->section->fresh()->SchoolLocations()->count());
    }

    /** @test */
    public function can_add_subject_to_section()
    {
        $schoolLocation = FactorySchoolLocation::create(FactorySchool::create('TestSchool')->school)->schoolLocation;
        $sectionFactory = FactorySection::create($schoolLocation, 'Arabisch');
        $startCount = $sectionFactory->section->fresh()->subjects->count();

        $baseSubjectArabisch = FactoryBaseSubject::find(67);
        $sectionFactory->addSubject($baseSubjectArabisch, 'Arabische Literatuur', 'ARAB-LIT');

        $this->assertEquals($startCount + 1, $sectionFactory->section->fresh()->subjects->count());
    }

    /** @test */
    public function can_add_multiple_subjects_to_a_section()
    {
        $sectionFactory = FactorySection::create(
            FactorySchoolLocation::create(
                FactorySchool::create(
                    'TestSchool'
                )->school
            )->schoolLocation,
            'Diversen'
        );

        $baseSubject1 = FactoryBaseSubject::getRandom();
        $baseSubject2 = FactoryBaseSubject::find(1);

        $sectionFactory
            ->addSubject($baseSubject1, 'Random vak', 'RAND')
            ->addSubject($baseSubject2, 'Nederlandse Taalkunde');

        $this->assertEquals(2, $sectionFactory->section->fresh()->subjects->count());
    }

    /** @test */
    public function can_get_random_BaseSubject()
    {
        $baseSubject = FactoryBaseSubject::getRandom();
        $baseSubjectFactory = FactoryBaseSubject::random();

        $this->assertInstanceOf('tcCore\BaseSubject', $baseSubject);
        $this->assertInstanceOf('tcCore\BaseSubject', $baseSubjectFactory->baseSubject);
    }

    /** @test */
    public function can_find_BaseSubject()
    {
        $baseSubject = FactoryBaseSubject::find(71); //71 = Chinees

        $this->assertInstanceOf('tcCore\BaseSubject', $baseSubject);
    }

    /** @test */
    public function can_create_Section_share_it_and_add_Subjects_to_it()
    {

        $school = FactorySchool::create('SectionOwner')->school;
        $schoolLocationFirst = FactorySchoolLocation::create($school)->schoolLocation;
        $schoolLocationSecond = FactorySchoolLocation::create($school)->schoolLocation;

        $startCount = $schoolLocationFirst->fresh()->schoolLocationSections->count();

        $baseSubject1 = FactoryBaseSubject::find(1);
        $baseSubject2 = FactoryBaseSubject::find(67);

        $sectionFactory = FactorySection::create($schoolLocationFirst, 'Talen')
            ->addSubject($baseSubject1, 'Nederlandse gramatica')
            ->addSubject($baseSubject2, 'Arabische gramatica')
            ->addSharedSchoolLocation($schoolLocationSecond)
        ;

//        dd(
//            $sectionFactory->section->getKey(),
//            DB::table('school_location_sections')->where('school_location_id', '>', 170)->get(),
//        );
//        dd($schoolLocationFirst->fresh()->schoolLocationSections);

        $this->assertEquals(1, $schoolLocationSecond->fresh()->sharedSections->count());
        $this->assertEquals($startCount+1, $schoolLocationFirst->fresh()->schoolLocationSections->count()); //also count DEMO section
        $this->assertEquals(2, $sectionFactory->section->fresh()->subjects->count());
    }
}
