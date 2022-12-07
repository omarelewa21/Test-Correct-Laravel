<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up()
    {
        \tcCore\FactoryScenarios\FactoryScenarioSchoolToetsenbakkerij::create();
    }
};