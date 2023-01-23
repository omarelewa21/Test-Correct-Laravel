<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Auth;
use tcCore\BaseSubject;
use tcCore\Lib\User\Factory;
use tcCore\Period;
use tcCore\SchoolYear;
use tcCore\Subject;
use tcCore\Teacher;
use tcCore\Test;
use tcCore\User;

class RegisterTestBankForSchoollocationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \Illuminate\Support\Facades\DB::table('school_locations')
            ->update(['allow_new_test_bank' => 1]);

    }
}
