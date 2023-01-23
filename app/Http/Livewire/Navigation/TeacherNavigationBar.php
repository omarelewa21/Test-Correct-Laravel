<?php

namespace tcCore\Http\Livewire\Navigation;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use tcCore\Http\Livewire\NavigationBar;
use tcCore\Http\Traits\WithTeacherMenu;

class TeacherNavigationBar extends NavigationBar
{
    use WithTeacherMenu;

    public function render()
    {
        return view('livewire.navigation.teacher-navigation-bar');
    }

    protected function handleMenuFilters()
    {
        if (Auth::user()->isValidExamCoordinator()) {
            $this->filterMenuForExamCoordinator();
        }
        if (Gate::allows('useNewTakenTestsOverview')) {
            $this->filterMenuForNewTakenTestsOverview();
        }
        if (Auth::user()->isToetsenbakker()) {
            $this->filterMenuForToetsenbakker();
        }
    }

    private function filterMenuForExamCoordinator()
    {
        $notAllowed = [
            'menus' => [
                'taken',
                'classes',
                'analyses',
//                'results',
            ],
            'tiles' => [
                'tests'   => ['my-uploads', 'question-bank', 'create-test'],
                'planned' => ['invigilating', 'ongoing-assignments'],
            ]
        ];

        $this->filterMenu($notAllowed);
    }


    private function filterMenuForNewTakenTestsOverview()
    {
        $this->menus = $this->menus->mapWithKeys(function ($menu, $menuName) {
            if ($menuName === 'taken') {
                $menu->hasItems = false;
                $menu->action = (object)[
                    'directive'  => 'wire',
                    'method'     => 'laravelRedirect',
                    'parameters' => route('teacher.test-takes', 'taken')
                ];
            }
            return [$menuName => $menu];
        });

    }

    private function filterMenuForToetsenbakker()
    {
        $this->tileGroups = $this->tileGroups->mapWithKeys(function ($tiles, $menuName) {
            if ($menuName === 'tests') {
                foreach($tiles as $tileName => $tile) {
                    if($tileName === 'my-uploads') {
                        $tile->action = (object)[
                            'directive'  => 'wire',
                            'method'     => 'laravelRedirect',
                            'parameters' => route('teacher.file-management.testuploads')
                        ];

                    }
                }
            }
            return [$menuName => $tiles];
        });

    }
}