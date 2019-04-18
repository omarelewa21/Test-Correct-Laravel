<?php
/**
 * Created by PhpStorm.
 * User: erik
 * Date: 12/04/2019
 * Time: 13:29
 */

$factory->define(\tcCore\Text2speech::class, function (Faker\Generator $faker) {
    return [
        'acceptedby' => 2,
        'active' => true,
        'price' => 6.01
    ];
});