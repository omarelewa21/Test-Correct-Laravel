<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use tcCore\EanCode;

class EanCodeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        EanCode::create([
            'ean' => '9999999999444',
            'description' =>'dummy code for acceptance test',
        ]);
    }
}
