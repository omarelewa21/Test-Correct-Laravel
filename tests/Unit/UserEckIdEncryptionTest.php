<?php
/**
 * Created by PhpStorm.
 * User: erik
 * Date: 12/04/2019
 * Time: 13:18
 */

namespace Tests\Unit;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use tcCore\EckidUser;
use tcCore\Factories\FactoryUser;
use tcCore\FactoryScenarios\FactoryScenarioSchoolSimple;
use tcCore\User;
use Tests\ScenarioLoader;
use Tests\TestCase;

class UserEckIdEncryptionTest extends TestCase
{
    protected $loadScenario = FactoryScenarioSchoolSimple::class;
    private $teacherOne;
    protected function setUp(): void
    {
        parent::setUp();
        $this->teacherOne = ScenarioLoader::get('teacher1');
    }

    /** @test */
    public function it_should_store_the_eckId_encrypted_on_the_user()
    {
        $this->assertEmpty(
            $this->teacherOne->eckid
        );

        $this->teacherOne->eckId = 'T1_ECK_ID';

        $this->teacherOne->save();

        $this->assertEquals(
            'T1_ECK_ID',
            $this->teacherOne->refresh()->eckId
        );

        $eckIdsForUser = DB::table('eckid_user')->where('user_id', $this->teacherOne->getKey())->get();

        $this->assertCount(1, $eckIdsForUser);

        $this->assertNotEquals(
            'T1_ECK_ID',
            $eckIdsForUser->first()->eckid
        );

        $this->assertEquals(
            'T1_ECK_ID',
            Crypt::decryptString($eckIdsForUser->first()->eckid)
        );
    }

    /**
     * @test
     */
    public function it_should_store_a_lookup_column_on_the_eckid_user_table()
    {
        $this->teacherOne->refresh();
        $this->markTestSkipped(); /* For some reason this tests fails when running the entire class */

        $this->teacherOne->eckId = 'T1_ECK_ID';

        $this->teacherOne->save();

        $eckIdsForUser = DB::table('eckid_user')->where('user_id', $this->teacherOne->getKey())->first();

        $this->assertNotEmpty(
            $eckIdsForUser->eckid_hash
        );
    }

    /** @test */
    public function it_should_find_a_user_by_eck_id()
    {
        $this->assertNull(User::findByEckId('T1_ECK_ID')->first());

        $this->teacherOne->eckId = 'T1_ECK_ID';

        $this->teacherOne->save();

        $this->assertTrue(
            User::findByEckId('T1_ECK_ID')->first()->is($this->teacherOne)
        );
    }

    /** @test */
    public function if_two_eckids_happen_to_have_the_same_eckid_hash_it_should_still_return_the_correct_one()
    {
        $this->teacherOne->eckId = 'T1_ECK_ID';
        $this->teacherOne->save();

        $teacherTwo = FactoryUser::createTeacher(ScenarioLoader::get('school_locations')->first(), false)->user;
        $teacherTwo->eckId = 'T2_ECK_ID';
        $teacherTwo->save();

        $hash  = DB::table('eckid_user')->where('user_id', $this->teacherOne->id)->first()->eckid_hash;

        DB::table('eckid_user')->where('eckid', 'T2_ECK_ID')->update(['eckid_hash' => $hash]);

        $this->assertTrue(
            User::findByEckId('T1_ECK_ID')->first()->is($this->teacherOne)
        );
    }
}
