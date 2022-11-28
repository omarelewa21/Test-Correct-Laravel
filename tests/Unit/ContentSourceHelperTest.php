<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Gate;
use tcCore\Http\Helpers\ContentSourceHelper;
use tcCore\SchoolLocation;
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

        //check if user has all permissions
        $this->assertTrue($user->schoolLocation->allow_creathlon);
        $this->assertTrue($user->schoolLocation->show_national_item_bank);
        $this->assertTrue($user->hasSharedSections());

        //check if the Helper methods work
        $this->assertTrue(ContentSourceHelper::canViewContent($user,$publisherName));
    }

    /**
     * @test
     * @dataProvider publisherNamesDataSet
     */
    public function cannot_view_content($publisherName)
    {
        $user = $this->setupUserPermissions(false);
        \Auth::login($user);
        $user->schoolLocation->allow_creathlon = false;

        //check if user has all permissions
        $this->assertFalse($user->schoolLocation->allow_creathlon);
        $this->assertFalse($user->schoolLocation->show_national_item_bank);
        $this->assertFalse($user->hasSharedSections());


        //check if the Helper Methods work
        $this->assertFalse(ContentSourceHelper::canViewContent($user, $publisherName));

    }

    public function publisherNamesDataSet(): array
    {
        return [
            'national'  => ['national'],
            'umbrella'  => ['umbrella'],
            'creathlon' => ['creathlon'],
        ];
    }

    private function setupUserPermissions($allowEverything = true)
    {
        $user = User::find(1486);

        if($allowEverything)
        {
            $school_location = SchoolLocation::where('id', '<>', $user->schoolLocation->id)->first();
            $section = $school_location->schoolLocationSections->first()->section;
            try{
                $user->schoolLocation->sharedSections()->attach($section);
            } catch (\Exception $e) {}
        }
        else
        {
            if($user->schoolLocation->sharedSections()->count())
            {
                $user->schoolLocation->sharedSections()->delete();
            }
        }

        $user->schoolLocation->allow_creathlon = $allowEverything;
        $user->schoolLocation->show_national_item_bank = $allowEverything;
        $user->schoolLocation->save();
        return $user;
    }
}