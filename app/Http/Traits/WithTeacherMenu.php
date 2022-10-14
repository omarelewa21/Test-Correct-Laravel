<?php

namespace tcCore\Http\Traits;

use Illuminate\Support\Arr;

trait WithTeacherMenu
{
    private function menus()
    {
        $menus = [];
        $menus['dashboard'] = [
            'hasItems' => false,
            'title'    => __('header.Dashboard'),
            'action'   => [
                'directive'  => 'wire',
                'method'     => 'cakeRedirect',
                'parameters' => ['dashboard']
            ]
        ];
        $menus['tests'] = [
            'hasItems' => true,
            'title'    => __('header.Toetsen'),
        ];
        $menus['planned'] = [
            'hasItems' => true,
            'title'    => __('header.Ingepland'),
        ];
        $menus['taken'] = [
            'hasItems' => true,
            'title'    => __('header.Afgenomen'),
        ];
        $menus['results'] = [
            'hasItems' => false,
            'title'    => __('header.Resultaten'),
            'action'   => [
                'directive'  => 'wire',
                'method'     => 'cakeRedirect',
                'parameters' => 'results.rated'
            ]
        ];
        $menus['analyses'] = [
            'hasItems' => true,
            'title'    => __('header.Analyses'),
        ];
        $menus['classes'] = [
            'hasItems' => true,
            'title'    => __('header.Klassen'),
        ];

        return collect(json_decode(json_encode($menus)));
    }

    private function tiles()
    {
        $tiles = $this->menus->where('hasItems', true)->mapWithKeys(function ($menuData, $menuName) {
            $getter = $menuName . 'Tiles';
            return [$menuName => self::$getter()];
        });

        return collect(json_decode(json_encode($tiles)));
    }

    private static function testsTiles()
    {
        $tiles = [];
        $tiles['create-test'] = [
            'title'  => __('header.Toets creëren'),
            'action' => [
                'directive'  => 'wire',
                'method'     => '$emit',
                'parameters' => ['openModal', 'teacher.test-start-create-modal']
            ]
        ];
        $tiles['test-bank'] = [
            'title'  => __('header.Toetsenbank'),
            'action' => [
                'directive'  => 'wire',
                'method'     => 'laravelRedirect',
                'parameters' => route('teacher.tests')
            ]
        ];
        $tiles['question-bank'] = [
            'title'  => __('header.Vragenbank'),
            'action' => [
                'directive'  => 'wire',
                'method'     => 'cakeRedirect',
                'parameters' => 'tests.question_bank'
            ]
        ];
        $tiles['my-uploads'] = [
            'title'  => __('header.Mijn uploads'),
            'action' => [
                'directive'  => 'wire',
                'method'     => 'cakeRedirect',
                'parameters' => 'tests.my_uploads'
            ]
        ];
        return $tiles;
    }

    private static function plannedTiles()
    {
        $tiles = [];
        $tiles['planned-tests'] = [
            'title'  => __('header.Mijn ingeplande toetsen'),
            'action' => [
                'directive'  => 'wire',
                'method'     => 'cakeRedirect',
                'parameters' => 'planned.my_tests'
            ]
        ];
        $tiles['invigilating'] = [
            'title'  => __('header.Surveilleren'),
            'action' => [
                'directive'  => 'wire',
                'method'     => 'cakeRedirect',
                'parameters' => 'planned.surveillance'
            ]
        ];
        $tiles['ongoing-assignments'] = [
            'title'  => __('header.Lopende opdrachten'),
            'action' => [
                'directive'  => 'wire',
                'method'     => 'cakeRedirect',
                'parameters' => 'planned.assessment_open'
            ]
        ];
        return $tiles;
    }

    private static function takenTiles()
    {
        $tiles = [];

        $tiles['my-taken-tests'] = [
            'title'  => __('header.Mijn afgenomen toetsen'),
            'action' => [
                'directive'  => 'wire',
                'method'     => 'cakeRedirect',
                'parameters' => 'taken.test_taken'
            ]
        ];
        $tiles['normalizing'] = [
            'title'  => __('header.Nakijken & normeren'),
            'action' => [
                'directive'  => 'wire',
                'method'     => 'cakeRedirect',
                'parameters' => 'taken.normalize_test'
            ]
        ];

        return $tiles;
    }

    private static function classesTiles()
    {
        $tiles = [];
        $tiles['my-classes-classes'] = [
            'title'  => __('header.Mijn klassen'),
            'action' => [
                'directive'  => 'wire',
                'method'     => 'cakeRedirect',
                'parameters' => 'classes.my_classes'
            ]
        ];
        $tiles['my-schoollocations'] = [
            'title'  => __('header.Mijn schoollocatie'),
            'action' => [
                'directive'  => 'wire',
                'method'     => 'cakeRedirect',
                'parameters' => 'classes.my_schoollocation'
            ]
        ];
        return $tiles;
    }

    private static function analysesTiles()
    {
        $tiles = [];
        $tiles['my-students'] = [
            'title'  => __('header.Mijn studenten'),
            'action' => [
                'directive'  => 'wire',
                'method'     => 'cakeRedirect',
                'parameters' => 'analyses.students'
            ]
        ];
        $tiles['my-classes-analyses'] = [
            'title'  => __('header.Mijn klassen'),
            'action' => [
                'directive'  => 'wire',
                'method'     => 'cakeRedirect',
                'parameters' => 'analyses.classes'
            ]
        ];
        return $tiles;
    }
}