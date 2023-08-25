<?php

namespace tcCore\Http\Helpers;

use Illuminate\Support\Facades\Gate;

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
        $baseLookUpTable = [ //todo replace route names with correct routes (in laravel, comment out others)
            'dashboard'                                   => ['main' => 'dashboard', 'sub' => ''],
            'tests.create-test'                           => ['main' => 'tests', 'sub' => 'create-test'],
            'teacher.tests'                               => ['main' => 'tests', 'sub' => 'test-bank'],
            'tests.question-bank'                         => ['main' => 'tests', 'sub' => 'question-bank'],
            'teacher.file-management.testuploads'         => ['main' => 'tests', 'sub' => 'my-uploads'],
            'teacher.test-take.planned'                   => ['main' => 'planned', 'sub' => 'planned-tests'],
            'teacher.test-take.taking'                    => ['main' => 'planned', 'sub' => 'planned-tests'],
            'planned.invigilating'                        => ['main' => 'planned', 'sub' => 'invigilating'],
            'planned.ongoing-assignments'                 => ['main' => 'planned', 'sub' => 'ongoing-assignments'],
            'results'                                     => ['main' => 'results', 'sub' => ''],
            'analyses.my-students'                        => ['main' => 'analyses', 'sub' => 'my-students'],
            'analyses.my-classes-analyses'                => ['main' => 'analyses', 'sub' => 'my-classes-analyses'],
            'classes.my-classes-classes'                  => ['main' => 'classes', 'sub' => 'my-classes-classes'],
            'classes.my-schoollocations'                  => ['main' => 'classes', 'sub' => 'my-schoollocations'],
            'account-manager.school-locations'            => ['main' => 'lists', 'sub' => 'school_locations'],
            'account-manager.schools'                     => ['main' => 'lists', 'sub' => 'schools'],
            'account-manager.file-management.testuploads' => ['main' => 'files', 'sub' => 'test_uploads'],
        ];

        $additionalRoutes = Gate::allows('useNewTakenTestsOverview')
            ? ['teacher.test-takes' => ['main' => 'taken', 'sub' => '']]
            : [
                'taken.my-taken-tests' => ['main' => 'taken', 'sub' => 'my-taken-tests'],
                'taken.normalizing'    => ['main' => 'taken', 'sub' => 'normalizing'],
            ];

        $lookUpTable = array_merge($baseLookUpTable, $additionalRoutes);

        $currentRouteName = \Route::currentRouteName();

        return $lookUpTable[$currentRouteName] ?? ['main' => '', 'sub' => ''];
    }
}