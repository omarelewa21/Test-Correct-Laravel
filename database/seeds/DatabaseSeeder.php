<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class DatabaseSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		$this->call('EanCodeTableSeeder');
		$this->call('SearchFilterTableSeeder');
        $this->call(SwitchSchoolAccountTableSeeder::class);
        if(config('custom.enable_additional_seeders'))
        {
            $this->call(CitoAccountSeeder::class);
            $this->call(ExamSchoolSeeder::class);
            $this->call(CreathlonItemBankSeeder::class);
//            $this->call(NationalItemBankSeeder::class);
        }
        $this->call(RegisterTestBankForSchoollocationsSeeder::class);
	}
}
