<?php

use Illuminate\Database\Migrations\Migration;
use tcCore\Lib\User\Factory;
use tcCore\Role;
use tcCore\User;

class AddSupportRole extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $support = Role::make([
            'name' => 'Support'
        ]);
        $support->id = 11;
        $support->save();

        $userFactory = new Factory(new User());
        $user = $userFactory->generate([
            'username' => 'support@test-correct.nl',
            'name_first' => 'Support',
            'name' => 'Support',
            'user_roles' => [11],
        ]);

        $user->password = '$2y$10$09COG9gAoSoOCG/PlzQw7ePKPX6xD6EkvOvz42H1vUiFAz5zXr.Aq';
        $user->save();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
