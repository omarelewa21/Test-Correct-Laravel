<?php

namespace tcCore\Http\Livewire;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use tcCore\Http\Helpers\CakeRedirectHelper;
use tcCore\Http\Helpers\NavigationBarHelper;

abstract class NavigationBar extends Component
{
    public  $activeRoute;

    protected $listeners = ['redirectToCake' => 'cakeRedirect'];

    public $showSchoolSwitcher = false;
    public $menus = [];
    public $tileGroups = [];

    public function mount()
    {
        $this->activeRoute = NavigationBarHelper::getActiveRoute();
        $this->showSchoolSwitcher = Auth::user()->hasMultipleSchools();
        $this->menus = $this->menus();
        $this->tileGroups = $this->tiles();
        $this->handleMenuFilters();
    }

    public function render()
    {
        return view('livewire.navigation-bar')->layout('layouts.base');
    }

    public function cakeRedirect($cakeRouteName)
    {
        return CakeRedirectHelper::redirectToCake($cakeRouteName);
    }

    public function laravelRedirect($route)
    {
        return redirect($route);
    }

    public function getMenuAction($menu): string
    {
        if (!property_exists($menu, 'action')) return '';

        $actionParameters = collect(Arr::wrap($menu->action->parameters))
            ->map(fn($item) => is_string($item) ? '\'' . $item . '\'' : $item)
            ->join(',');

        return sprintf('%s:click=%s(%s)',
            $menu->action->directive ?? 'wire',
            $menu->action->method ?? 'laravelRedirect',
            $actionParameters
        );
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
                'tests'   => ['my-uploads','question-bank','create-test'],
                'planned' => ['invigilating', 'ongoing-assignments'],
            ]
        ];

        $this->filterMenu($notAllowed);
    }

    private function filterMenu($notAllowed)
    {
        if (isset($notAllowed['menus'])) {
            $this->menus = $this->menus->reject(function ($menuData, $menuName) use ($notAllowed) {
                return collect($notAllowed['menus'])->contains($menuName);
            });
        }

        if (isset($notAllowed['tiles'])) {
            $this->tileGroups = $this->tileGroups->mapWithKeys(function ($tileGroupData, $tileGroupName) use ($notAllowed) {
                if (collect($notAllowed['tiles'])->has($tileGroupName)) {
                    $tileGroupData = collect($tileGroupData)->reject(function ($value, $key) use ($notAllowed, $tileGroupName) {
                        return collect($notAllowed['tiles'][$tileGroupName])->contains($key);
                    });
                }

                return [$tileGroupName => $tileGroupData];
            });
        }
    }

    private function handleMenuFilters()
    {
        if (Auth::user()->isValidExamCoordinator()) {
            $this->filterMenuForExamCoordinator();
        }
        if (Gate::allows('useNewTakenTestsOverview')) {
            $this->filterMenuForNewTakenTestsOverview();
        }
    }

    private function filterMenuForNewTakenTestsOverview()
    {
        $this->menus = $this->menus->mapWithKeys(function ($menu, $menuName) {
            if ($menuName === 'taken') {
                $menu->hasItems = false;
                $menu->action = (object) [
                    'directive'  => 'wire',
                    'method'     => 'laravelRedirect',
                    'parameters' => route('teacher.test-takes', 'taken')
                ];
            }
            return [$menuName => $menu];
        });

    }
}
