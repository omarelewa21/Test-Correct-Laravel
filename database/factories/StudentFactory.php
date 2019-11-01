<?php
/**
 * Created by PhpStorm.
 * User: erik
 * Date: 26/04/2019
 * Time: 15:22
 */

$factory->define(\tcCore\Student::class, function (Faker\Generator $faker) {
    return [
        'user_id' => 1,
        'class_id' => 1
    ];
});