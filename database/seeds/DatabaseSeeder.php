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
        $this->call(CreathlonItemBankSeeder::class);
//        Disabled the seeder because it takes about a minute. To run in manually:
//        php artisan db:seed --class=NationalItemBankSeeder
//        $this->call(NationalItemBankSeeder::class);
        $this->call(RegisterTestBankForSchoollocationsSeeder::class);
	}
}
