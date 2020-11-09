<?php
/**
 * Created by PhpStorm.
 * User: erik
 * Date: 12/04/2019
 * Time: 13:29
 */

use tcCore\Text2Speech;

$factory->define(Text2Speech::class, function (Faker\Generator $faker) {
    return [
        'acceptedby' => 2,
        'active' => true,
        'price' => 6.01
    ];
});