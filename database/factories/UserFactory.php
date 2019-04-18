<?php
/**
 * Created by PhpStorm.
 * User: erik
 * Date: 12/04/2019
 * Time: 13:29
 */

$factory->define(\tcCore\User::class, function (Faker\Generator $faker) {
    return [
        'school_id' => 3,
        'school_location_id' => 3,
        'username' => $faker->email,
        'password' => bcrypt($faker->password),
        'name_first' => $faker->firstName,
        'name' => $faker->lastName,
        'send_welcome_email' => true,
    ];
});
