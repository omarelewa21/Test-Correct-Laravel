<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use Faker\Generator as Faker;
use tcCore\SearchFilter;

$factory->define(SearchFilter::class, function (Faker $faker) {
    return [
       	'name' => $faker->name,
       	'key' => $faker->name,
       	'filters' => json_encode(['name'=>$faker->name]),
       	'user_id' => function(){
       					return tcCore\User::first()->id;
       				},
    ];
});
