<?php

namespace tcCore\Http\Helpers;

class NavigationBarHelper
{
    /**
     * Get active Route for setting the right navigation bar button/tile on active.
     * fill lookUpTable like: 'route name' => ['main' => '', 'sub' => ''],
     *
     * @return string[]
     */
    public static function getActiveRoute()
    {
        $lookUpTable = [ //todo replace route names with correct routes (in laravel, comment out others)
            'dashboard' => ['main' => 'dashboard', 'sub' => ''],

            'tests.create-test' => ['main' => 'tests', 'sub' => 'create-test'],
            'teacher.tests' => ['main' => 'tests', 'sub' => 'test-bank'],
            'tests.question-bank' => ['main' => 'tests', 'sub' => 'question-bank'],
            'tests.my-uploads' => ['main' => 'tests', 'sub' => 'my-uploads'],

            'planned.planned-tests' => ['main' => 'planned', 'sub' => 'planned-tests'],
            'planned.invigilating' => ['main' => 'planned', 'sub' => 'invigilating'],
            'planned.ongoing-assignments' => ['main' => 'planned', 'sub' => 'ongoing-assignments'],

            'teacher.test-takes' => ['main' => 'taken', 'sub' => 'my-taken-tests'],
            'taken.normalizing' => ['main' => 'taken', 'sub' => 'normalizing'],

            'results' => ['main' => 'results', 'sub' => ''],

            'analyses.my-students' => ['main' => 'analyses', 'sub' => 'my-students'],
            'analyses.my-classes-analyses' => ['main' => 'analyses', 'sub' => 'my-classes-analyses'],

            'classes.my-classes-classes' => ['main' => 'classes', 'sub' => 'my-classes-classes'],
            'classes.my-schoollocations' => ['main' => 'classes', 'sub' => 'my-schoollocations'],
        ];

        if (isset($lookUpTable[\Route::currentRouteName()])) {
            return $lookUpTable[\Route::currentRouteName()];
        }
        return ['main' => '', 'sub' => ''];

    }


}