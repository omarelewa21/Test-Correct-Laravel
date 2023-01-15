<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use tcCore\FactoryScenarios\FactoryScenarioSchoolToetsenbakkerij;

class ToetsenbakkerSeeder extends Seeder
{
    public function run()
    {
        try {
            FactoryScenarioSchoolToetsenbakkerij::create();
        } catch (\Throwable $exception) {

        }
    }
}
