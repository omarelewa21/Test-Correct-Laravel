<?php

namespace Tests\Unit;


use tcCore\FactoryScenarios\FactoryScenarioContentSourceHelper;
use tcCore\FactoryScenarios\FactoryScenarioSchoolRandomComplexWithCreathlon;
use tcCore\Http\Helpers\ActingAsHelper;
use Illuminate\Support\Collection;
use tcCore\Factories\FactoryTest;
use tcCore\Http\Helpers\ContentSourceHelper;
use tcCore\SchoolLocation;
use tcCore\Services\ContentSource\FormidableService;
use tcCore\Services\ContentSource\OlympiadeArchiveService;
use tcCore\Services\ContentSource\ThiemeMeulenhoffService;
use tcCore\User;
use Tests\ScenarioLoader;
use tcCore\Services\ContentSource\CreathlonService;
use tcCore\Services\ContentSource\NationalItemBankService;
use tcCore\Services\ContentSource\OlympiadeService;
use tcCore\Services\ContentSource\PersonalService;
use tcCore\Services\ContentSource\SchoolLocationService;
use tcCore\Services\ContentSource\UmbrellaOrganizationService;
use Tests\TestCase;

class ContentSourceHelperTest extends TestCase
{
    protected $loadScenario = FactoryScenarioContentSourceHelper::class;



    protected function setUp(): void
    {
//        static::$skipRefresh = true;

        parent::setUp();
        ActingAsHelper::getInstance()->setUser(ScenarioLoader::get('teacher1'));
    }

    const EXPECTED_CONTENT_SOURCE_SERVICES = [
        'personal'        => PersonalService::class,
        'school_location' => SchoolLocationService::class,
        'umbrella'        => UmbrellaOrganizationService::class,
        'national'        => NationalItemBankService::class,
        'creathlon'       => CreathlonService::class,
        'olympiade'       => OlympiadeService::class,
        'olympiade_archive'=> OlympiadeArchiveService::class,
        'formidable'      => FormidableService::class,
    ];

    /*TODO
     * check ! isExamCoordinator on Personal and Umbrella
     */

    /** @test */
    public function canGetAllPublishableScopes()
    {
        $expectedScopes = [
            'exam',
            'ldt',
            'published_creathlon',
            'published_formidable',
            'published_thieme_meulenhoff',
            'published_olympiade',
            'published_olympiade_archive',
        ];

        $scopes = ContentSourceHelper::getPublishableScopes();

        $this->assertInstanceOf(Collection::class, $scopes);

        //assertEquals() instead of assertSame() because the order of the array is not important
        $this->assertEquals($expectedScopes, $scopes->toArray());
    }

    /** @test */
    public function canGetAllPublishableAbbreviations()
    {
        $expectedAbbreviations = [
            'EXAM',
            'LDT',
            'PUBLS',
            'FD',
            'TM',
            'SBON',
        ];

        $abbreviations = ContentSourceHelper::getPublishableAbbreviations();

        $this->assertInstanceOf(Collection::class, $abbreviations);

        //assertEquals() instead of assertSame() because the order of the array is not important
        $this->assertEquals($expectedAbbreviations, $abbreviations->toArray());
    }

    /**
     * @test
     * @dataProvider contentSourceDataset
     */
    public function contentSourceServiceTest(
        $serviceClass,
        $expectedTranslation,
        $expectedHighlightEnabled,
        $expectedContentSourceAvailable,
        $expectedTabName,
    )
    {
        //relies on auth user..
        $user = $this->setupUserPermissions();

        //assert translation
        $this->assertEquals($expectedTranslation, $serviceClass::getTranslation());

        //assert checking available for user
        // allowed for user (schoolLocation) and available tests for the user
        $this->assertEquals($expectedContentSourceAvailable, $serviceClass::isAvailableForUser($user));

        $this->assertEquals($expectedHighlightEnabled, $serviceClass::highlightTab());

        $this->assertEquals($expectedTabName, $serviceClass::getName());
    }

    /**
     * @test
     * @dataProvider publisherNamesDataSet
     */
    public function can_view_content($publisherName)
    {
        $user = $this->setupUserPermissions();
        \Auth::login($user);

        //check if user has all permissions
        $this->assertTrue($user->schoolLocation->allow_creathlon);
        $this->assertTrue($user->schoolLocation->show_national_item_bank);
        $this->assertTrue($user->hasSharedSections());

        //check if the Helper methods work
        $this->assertTrue(
            ContentSourceHelper::canViewContent($user, $publisherName)
        );
    }

    /** @test */
    public function canViewAllContent()
    {
        $user = $this->setupUserPermissions();
        auth()->login($user);
        $this->actingAs($user);

        $this->assertEquals([
            "personal"        => "tcCore\Services\ContentSource\PersonalService",
            "school_location" => "tcCore\Services\ContentSource\SchoolLocationService",
            "umbrella"        => "tcCore\Services\ContentSource\UmbrellaOrganizationService",
            "national"        => "tcCore\Services\ContentSource\NationalItemBankService",
            "creathlon"       => "tcCore\Services\ContentSource\CreathlonService",
            "olympiade"       => "tcCore\Services\ContentSource\OlympiadeService",
            "olympiade_archive"=> "tcCore\Services\ContentSource\OlympiadeArchiveService",
        ],
            ContentSourceHelper::allAllowedForUser($user)->toArray()
        );

    }

    /** @test */
    public function canGetAllPublishableTestScopes()
    {
        $user = $this->setupUserPermissions();
        auth()->login($user);
        $this->actingAs($user);

        $expectedScopes = [
            'exam',
            'ldt',
            'published_creathlon',
            'published_olympiade',
            'published_olympiade_archive',
        ];

        $this->assertTrue(ContentSourceHelper::getPublishableScopes() instanceof Collection);

        $this->assertEquals(
            expected: 0,
            actual: ContentSourceHelper::getPublishableScopes()->diff($expectedScopes)->count()
        );
    }

    /** @test */
    public function canGetAllPublishableTestAbbreviations()
    {
        $user = $this->setupUserPermissions();
        auth()->login($user);
        $this->actingAs($user);

        $expectedAbbreviations = [
            "EXAM",
            "LDT",
            "PUBLS",
            "SBON",
            "TM",
            'FM',
        ];

        $this->assertTrue(ContentSourceHelper::getPublishableAbbreviations() instanceof Collection);

        $this->assertEquals(
            expected: 0,
            actual: ContentSourceHelper::getPublishableAbbreviations()->diff($expectedAbbreviations)->count()
        );
    }

    /** @test */
    public function canGetAllAvailableContentSourceHelpersStraightFromTheDirectory()
    {
        // assertEquals is not strict on the order of the array,
        //  getAllAvailableContentSourceServices() is probably in a incorrect order
        $this->assertEquals(
            self::EXPECTED_CONTENT_SOURCE_SERVICES,
            $this->callPrivateMethod(new ContentSourceHelper, 'getAllAvailableContentSourceServices')->toArray()
        );

        //assertSame IS strict on the order of the array
        // getAvailableSourcesInCorrectOrder() is suppossed to return the items in correct order
        $this->assertSame(
            self::EXPECTED_CONTENT_SOURCE_SERVICES,
            $this->callPrivateMethod(new ContentSourceHelper, 'getAvailableSourcesInCorrectOrder')->toArray()
        );
    }

    /**
     * @test
     * @dataProvider publisherNamesDataSet
     */
    public function cannot_view_content_if_not_allowed_for_school_location_or_user($publisherName)
    {
        $user = $this->setupUserPermissions(false);
        \Auth::login($user);
        $user->schoolLocation->allow_creathlon = false;

        //check if user has all permissions
        $this->assertFalse($user->schoolLocation->allow_creathlon);
        $this->assertFalse($user->schoolLocation->show_national_item_bank);
        $this->assertFalse($user->hasSharedSections());


        if (in_array($publisherName, ['personal', 'school_location'])) {
            //public and school_location are always allowed
            return $this->assertTrue(ContentSourceHelper::canViewContent($user, $publisherName));
        }

        $this->assertFalse(ContentSourceHelper::canViewContent($user, $publisherName));
    }

    /**
     * @test
     * @dataProvider publisherNamesDataSet
     */
    public function cannot_view_personal_or_umbrella_if_exam_coordinator_but_can_still_view_the_rest($publisherName)
    {
        $user = $this->setupUserPermissions(true);
        auth()->login($user);

        //can view everything, while not yet a valid examCoordinator
        $this->assertTrue(ContentSourceHelper::canViewContent($user, $publisherName));
        $this->assertFalse($user->isValidExamCoordinator());

        //make the user a valid examcoordinator
        $user->is_examcoordinator = true;
        $user->is_examcoordinator_for = 'SCHOOL';
        $user->save();
        $this->assertTrue($user->isValidExamCoordinator());

        //assert cannot see personal and umbrella, but can still see the rest
        if (in_array($publisherName, ['personal', 'umbrella'])) {
            return $this->assertFalse(ContentSourceHelper::canViewContent($user, $publisherName));
        }
        $this->assertTrue(ContentSourceHelper::canViewContent($user, $publisherName));
    }

    /**
     * Data Providers
     */
    public function publisherNamesDataSet(): array
    {
        return [
            'personal'          => ['personal'],
            'school_location'   => ['school_location'],
            'umbrella'          => ['umbrella'],
            'national'          => ['national'],
            'creathlon'         => ['creathlon'],
            'olympiade'         => ['olympiade'],
            'olympiade_archive' => ['olympiade_archive'],
            'thieme_meulenhoff' => ['thieme_meulenhoff'],
            'formidable'        => ['formidable'],
        ];
    }

    public function contentSourceDataset()
    {
        return [
            'Personal'          => [
                'class'       => PersonalService::class, //todo check it is not available for a valid examCoordinator
                'translation' => 'Persoonlijk',
                'highlight'   => false,
                'available'   => true,
                'tabName'     => 'personal',
            ],
            'school_location'   => [
                'class'       => SchoolLocationService::class,
                'translation' => 'School',
                'highlight'   => false,
                'available'   => true,
                'tabName'     => 'school_location',
            ],
            'umbrella'          => [
                'class'       => UmbrellaOrganizationService::class,
                'translation' => 'Scholengemeenschap',
                'highlight'   => false,
                'available'   => true,
                'tabName'     => 'umbrella',
            ],
            'national'          => [
                'class'       => NationalItemBankService::class,
                'translation' => 'Nationaal',
                'highlight'   => true,
                'available'   => true,
                'tabName'     => 'national',
            ],
            'creathlon'         => [
                'class'       => CreathlonService::class,
                'translation' => 'Creathlon',
                'highlight'   => true,
                'available'   => true,
                'tabName'     => 'creathlon',
            ],
            'olympiade'         => [
                'class'       => OlympiadeService::class,
                'translation' => 'Olympiade',
                'highlight'   => true,
                'available'   => true,
                'tabName'     => 'olympiade',
            ],
            'olympiade_archive' => [
                'class'       => OlympiadeService::class,
                'translation' => 'Olympiade Archive',
                'highlight'   => true,
                'available'   => true,
                'tabName'     => 'olympiade_archive',
            ],
            'thieme_meulenhoff' => [
                'class'       => ThiemeMeulenhoffService::class,
                'translation' => 'Thieme Meulenhoff',
                'highlight'   => true,
                'available'   => true,
                'tabName'     => 'thieme_meulenhoff',
            ],
            'formidable'        => [
                'class'       => FormidableService::class,
                'translation' => 'Formidable',
                'highlight'   => true,
                'available'   => true,
                'tabName'     => 'formidable',
            ],
        ];
    }

    private function setupUserPermissions($allowEverything = true)
    {
        $user = ScenarioLoader::get('teacher1');

        if ($allowEverything) {
            $school_location = SchoolLocation::where('id', '<>', $user->schoolLocation->id)->first();
            $section = $school_location->schoolLocationSections->first()->section;

            $subject = $section->subjects->first();

            //shared section test
            $teacherUser = $school_location->users()->whereRelation('roles', 'name', '=', 'Teacher')->first();
            FactoryTest::create($teacherUser)->setProperties(['subject_id' => $subject->id]);

            //content source tests
            $this->createTestsForContentSources($subject->getKey(), $teacherUser);

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
        $user->schoolLocation->allow_olympiade_archive = $allowEverything;
        $user->schoolLocation->show_national_item_bank = $allowEverything;

        $user->schoolLocation->allow_tm_biology = $allowEverything;
        $user->schoolLocation->allow_tm_geography = $allowEverything;
        $user->schoolLocation->allow_tm_dutch = $allowEverything;
        $user->schoolLocation->allow_tm_english = $allowEverything;
        $user->schoolLocation->allow_tm_french = $allowEverything;

        $user->schoolLocation->allow_formidable = $allowEverything;

        $user->schoolLocation->save();
        return $user;
    }

    private function createTestsForContentSources($subjectId, User $user)
    {
        FactoryTest::create($user)->setProperties(['subject_id' => $subjectId, 'scope' => 'cito']);
        FactoryTest::create($user)->setProperties(['subject_id' => $subjectId, 'scope' => 'exam']);
        FactoryTest::create($user)->setProperties(['subject_id' => $subjectId, 'scope' => 'ldt']);
        FactoryTest::create($user)->setProperties(['subject_id' => $subjectId, 'scope' => 'published_creathlon']);
        FactoryTest::create($user)->setProperties(['subject_id' => $subjectId, 'scope' => 'published_olympiade']);
        FactoryTest::create($user)->setProperties(['subject_id' => $subjectId, 'scope' => 'published_olympiade_archive']);
    }

}