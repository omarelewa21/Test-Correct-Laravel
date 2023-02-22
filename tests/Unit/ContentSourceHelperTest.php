<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Gate;
use tcCore\Factories\FactoryTest;
use tcCore\Http\Helpers\ActingAsHelper;
use tcCore\Http\Helpers\ContentSourceHelper;
use tcCore\SchoolLocation;
use tcCore\Test;
use tcCore\User;
use Tests\TestCase;

class ContentSourceHelperTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @test
     * @dataProvider publisherNamesDataSet
     */
    public function can_view_content($publisherName)
    {
        $user = $this->setupUserPermissions();
        \Auth::login($user);
        $this->actingAs($user);

        //check if user has all permissions
        $this->assertTrue($user->schoolLocation->allow_creathlon);
        $this->assertTrue($user->schoolLocation->show_national_item_bank);
        $this->assertTrue($user->hasSharedSections());

        //check if the Helper methods work
        $this->assertTrue(ContentSourceHelper::canViewContent($user, $publisherName));
    }

    /** @test */
    public function canViewAllContent()
    {
        $user = $this->setupUserPermissions();
        \Auth::login($user);
        $this->actingAs($user);

        $this->assertEquals([
            'personal',
            'school_location',
            'umbrella',
            'national',
            'creathlon',
            'olympiade',
        ],
            ContentSourceHelper::allAllowedForUser($user)->toArray()
        );

    }

    /**
     * @test
     * @dataProvider publisherNamesDataSet
     */
    public function cannot_view_content($publisherName)
    {
        $user = $this->setupUserPermissions(false);
        \Auth::login($user);

        //check if user has all permissions
        $this->assertFalse($user->schoolLocation->allow_creathlon);
        $this->assertFalse($user->schoolLocation->show_national_item_bank);
        $this->assertFalse($user->hasSharedSections());


        //check if the Helper Methods work
        $this->assertFalse(ContentSourceHelper::canViewContent($user, $publisherName));

    }

    /**
     * @test
     * @dataProvider publisherNamesDataSet
     */
    public function canGetTestsAvailable($publisherName)
    {
        $user = $this->setupUserPermissions();

        $this->assertTrue(ContentSourceHelper::testsAvailable($user, $publisherName));
    }

    public function publisherNamesDataSet(): array
    {
        return [
            'national'  => ['national'],
            'umbrella'  => ['umbrella'],
            'creathlon' => ['creathlon'],
            'olympiade' => ['olympiade'],
        ];
    }

    private function setupUserPermissions($allowEverything = true)
    {
        $user = User::find(1486);
        \Auth::login($user);


        if ($allowEverything) {
            $school_location = SchoolLocation::where('id', '<>', $user->schoolLocation->id)->first();
            $section = $school_location->schoolLocationSections->first()->section;

            $subject = $section->subjects->first();

            //shared section test
            $teacherUser = $school_location->users()->whereRelation('roles', 'name', '=', 'Teacher')->first();
            FactoryTest::create($teacherUser)->setProperties(['subject_id' => $subject->id]);

            //content source tests
            $this->createTestsForContentSources($subject->getKey());

            $this->actingAs($user);
            ActingAsHelper::getInstance()->setUser($user);

            try {
                $user->schoolLocation->sharedSections()->attach($section);
            } catch (\Exception $e) {
                dd($e);
            }
        } else {
            if ($user->schoolLocation->sharedSections()->count()) {
                $user->schoolLocation->sharedSections()->delete();
            }
        }

        $user->schoolLocation->allow_creathlon = $allowEverything;
        $user->schoolLocation->allow_olympiade = $allowEverything;
        $user->schoolLocation->show_national_item_bank = $allowEverything;
        $user->schoolLocation->save();
        return $user;
    }

    public function createTestsForContentSources($subjectId)
    {
        FactoryTest::create()->setProperties(['subject_id' => $subjectId, 'scope' => 'cito']);
        FactoryTest::create()->setProperties(['subject_id' => $subjectId, 'scope' => 'exam']);
        FactoryTest::create()->setProperties(['subject_id' => $subjectId, 'scope' => 'ldt']);
        FactoryTest::create()->setProperties(['subject_id' => $subjectId, 'scope' => 'published_creathlon']);
        FactoryTest::create()->setProperties(['subject_id' => $subjectId, 'scope' => 'published_olympiade']);
    }
}