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
use tcCore\ArchivedModel;
use tcCore\EckidUser;
use tcCore\SchoolClass;
use tcCore\SchoolLocation;
use tcCore\Teacher;
use tcCore\TestTake;
use tcCore\User;
use Tests\TestCase;

class UserEckIdEncryptionTest extends TestCase
{
    use \Illuminate\Foundation\Testing\DatabaseTransactions;

    /** @test */
    public function it_should_store_the_eckId_encrypted_on_the_user()
    {
        $teacherOne = User::where('username', 'd1@test-correct.nl')->first();

        $this->assertEmpty(
            $teacherOne->eckid
        );

        $teacherOne->eckId = 'T1_ECK_ID';

        $teacherOne->save();

        $this->assertEquals(
            'T1_ECK_ID',
            $teacherOne->refresh()->eckId
        );

        $eckIdsForUser = DB::table('eckid_user')->where('user_id', $teacherOne->getKey())->get();

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

    /** @test */
    public function it_should_store_a_lookup_column_on_the_eckid_user_table()
    {
        $teacherOne = User::where('username', 'd1@test-correct.nl')->first();
        $teacherOne->eckId = 'T1_ECK_ID';

        $teacherOne->save();

        $eckIdsForUser = DB::table('eckid_user')->where('user_id', $teacherOne->getKey())->first();

        $this->assertNotEmpty(
            $eckIdsForUser->eckid_hash
        );
    }

    /** @test */
    public function it_should_find_a_user_by_eck_id()
    {
        $this->assertNull(User::findByEckId('T1_ECK_ID')->first());

        $teacherOne = User::where('username', 'd1@test-correct.nl')->first();
        $teacherOne->eckId = 'T1_ECK_ID';

        $teacherOne->save();

        $this->assertTrue(
            User::findByEckId('T1_ECK_ID')->first()->is($teacherOne)
        );
    }

    /** @test */
    public function if_two_eckids_happen_to_have_the_same_eckid_hash_it_should_still_return_the_correct_one()
    {
        $teacherOne = User::where('username', 'd1@test-correct.nl')->first();
        $teacherOne->eckId = 'T1_ECK_ID';
        $teacherOne->save();

        $teacherTwo = User::where('username', 'd2@test-correct.nl')->first();
        $teacherTwo->eckId = 'T2_ECK_ID';
        $teacherTwo->save();

        $hash  = DB::table('eckid_user')->where('user_id', $teacherOne->id)->first()->eckid_hash;

        DB::table('eckid_user')->where('eckid', 'T2_ECK_ID')->update(['eckid_hash' => $hash]);

        $this->assertTrue(
            User::findByEckId('T1_ECK_ID')->first()->is($teacherOne)
        );
    }
}
