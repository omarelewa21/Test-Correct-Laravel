<?php
/**
 * Created by PhpStorm.
 * User: erik
 * Date: 12/04/2019
 * Time: 13:30
 */


use tcCore\Text2SpeechLog;

$factory->define(Text2SpeechLog::class, function (Faker\Generator $faker) {
    return [
        'who' => 2,
        'action' => 'ACCEPTED'
    ];
});
