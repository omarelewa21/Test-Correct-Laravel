<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use tcCore\Lib\User\Factory;
use tcCore\Role;
use tcCore\User;

class AddTechAdministrator extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tech = Role::make([
           'name' => 'Tech administrator'
        ]);
        $tech->id = 10;
        $tech->save();

        $userFactory = new Factory(new User());
        $user = $userFactory->generate([
            'username' => 'tech@test-correct.nl',
            'name_first' => 'Tech',
            'name' => 'Administrator',
            'user_roles' => [10],
        ]);

        $user->password = '$2y$10$iOFRSOgXeE28lGy3qZPOCufWTbsOYFG/lsujAN7kMEvzt8CeGEa.a';
        $user->save();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        User::whereUsername('tech@test-correct.nl')->forceDelete();
        Role::whereName('Tech administrator')->forceDelete();
    }
}
