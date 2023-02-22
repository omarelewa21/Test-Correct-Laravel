<?php

namespace Tests\Unit;

use Database\Seeders\OlympiadeItemBankSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Gate;
use tcCore\Factories\FactoryTest;
use tcCore\Http\Helpers\ActingAsHelper;
use tcCore\Http\Helpers\ContentSourceHelper;
use tcCore\School;
use tcCore\SchoolLocation;
use tcCore\Test;
use tcCore\User;
use Tests\TestCase;

class ContentSourceSeedersTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function can_seed_olympiade_content()
    {
        $user = User::find(1486);
        \Auth::login($user);
        $this->actingAs($user);

        if (SchoolLocation::where('customer_code', '=', config('custom.olympiade_school_customercode'))
            ->exists()) {
            throw new \Exception('Cannot seed Olympia content schoollocation because it already exists');
        }

        (new OlympiadeItemBankSeeder)->run();

        //assert correct author username used for teacher user
        $this->assertTrue(
            User::where('username', '=', config('custom.olympiade_school_author'))
                ->exists()
        );
        //assert correct customerCode used for schoolLocation
        $this->assertTrue(
            SchoolLocation::where('customer_code', '=', config('custom.olympiade_school_customercode'))
                ->exists()
        );
        //assert published tests
        $this->assertTrue(
            Test::where('scope', '=', 'published_olympiade')
                ->exists()
        );
        //assert unpublished tests
        $this->assertTrue(
            Test::where('scope', '=', 'not_published_olympiade')
                ->exists()
        );
    }

}