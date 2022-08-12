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
        $this->call(CitoAccountSeeder::class);
        $this->call(ExamSchoolSeeder::class);
        $this->call(NationalItemBankSeeder::class);
        $this->call(RegisterTestBankForSchoollocationsSeeder::class);
	}
}
