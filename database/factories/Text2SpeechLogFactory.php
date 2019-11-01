<?php
/**
 * Created by PhpStorm.
 * User: erik
 * Date: 12/04/2019
 * Time: 13:30
 */


$factory->define(\tcCore\Text2speechLog::class, function (Faker\Generator $faker) {
    return [
        'who' => 2,
        'action' => 'ACCEPTED'
    ];
});
