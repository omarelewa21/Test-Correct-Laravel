<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use Faker\Generator as Faker;
use tcCore\SchoolClass;

$factory->define(SchoolClass::class, function (Faker $faker) {
    return [
        'school_location_id' => 1,
        'education_level_id' => 1,
        'school_year_id' => 1,
        'name' => $faker->company,
        'education_level_year' => 1,
        'is_main_school_class' => 0,
        'do_not_overwrite_from_interface' => 0,
        'demo' => 0
    ];
});
