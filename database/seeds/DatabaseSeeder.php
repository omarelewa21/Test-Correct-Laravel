<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class DatabaseSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(EanCodeTableSeeder::class);
        $this->call(SearchFilterTableSeeder::class);
        $this->call(SwitchSchoolAccountTableSeeder::class);
        if (config('custom.enable_additional_seeders')) {
            $this->call(CitoAccountSeeder::class);
            $this->call(ExamSchoolSeeder::class);
            $this->call(CreathlonItemBankSeeder::class);
            $this->call(OlympiadeItemBankSeeder::class);
            $this->call(OlympiadeArchiveItemBankSeeder::class);
//            $this->call(NationalItemBankSeeder::class);
            $this->call(FormidableItemBankSeeder::class);
            $this->call(ToetsenbakkerSeeder::class);
            $this->call(ThiemeMeulenhoffItemBankSeeder::class);
        }
        $this->call(RegisterTestBankForSchoollocationsSeeder::class);
    }
}
