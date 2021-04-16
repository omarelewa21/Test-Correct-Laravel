<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use tcCore\SchoolLocation;
use tcCore\Teacher;
use tcCore\User;

class AddAllTeachersToSchoolLocationUserTableAndAddExternalId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        User::withRoleTeacher()->chunk(1, function($users) {
            $users->map(function ($user) {
                if ($user->isA('teacher') && !is_null($user->school_location_id)) {
                    $user->addSchoolLocation($user->schoolLocation);
                }
            });
        });

      DB::statement('UPDATE school_location_user set external_id = (select external_id from users where  users.id = school_location_user.user_id)');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

    }
}
