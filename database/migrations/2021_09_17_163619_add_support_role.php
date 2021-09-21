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

        if (config('app.env', 'local') == 'local') {
            $userFactory = new Factory(new User());
            $user = $userFactory->generate([
                'username' => 'sobitbv+support@hotmail.com',
                'name_first' => 'Sobit',
                'name_suffix' => 'bv',
                'name' => 'Support',
                'user_roles' => [11],
            ]);

            $user->password = '$2y$10$09COG9gAoSoOCG/PlzQw7ePKPX6xD6EkvOvz42H1vUiFAz5zXr.Aq';
            $user->save();
        }
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
