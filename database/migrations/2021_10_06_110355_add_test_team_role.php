<?php

use Illuminate\Database\Migrations\Migration;
use tcCore\Lib\User\Factory;
use tcCore\Role;


class AddTestTeamRole extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $support = Role::make([
            'name' => 'Test team'
        ]);
        $support->id = 12;
        $support->save();

        if (config('app.env', 'local') == 'local') {
            $userFactory = new Factory();
            $user = $userFactory->generate([
                'username' => 'sobitbv+testteam@hotmail.com',
                'name_first' => 'Sobit',
                'name_suffix' => 'bv',
                'name' => 'Test Team',
                'user_roles' => [12],
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
        DB::table('roles')->where('name', '=', 'Test team')->delete();
        DB::table('users')->where('username', '=', 'sobitbv+testteam@hotmail.com')->delete();
    }
}
