<?php

namespace tcCore\Http\Livewire;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use tcCore\Http\Controllers\TemporaryLoginController;
use tcCore\Http\Helpers\NavigationBarHelper;
use tcCore\Http\Traits\WithTeacherMenu;

class NavigationBar extends Component
{
    use WithTeacherMenu;

    public $activeRoute;

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
        $url = $this->createCakeUrl($cakeRouteName);
        redirect($url);
    }

    protected function getCakeUrlString($cakePage)
    {
        $lookUpArray = [
            'dashboard'                   => '/users/welcome',
            'tests.test_bank'             => '/tests/index',
            'tests.question_bank'         => '/questions/index',
            'tests.my_uploads'            => '/file_management/testuploads',
            'tests.my_uploads_with_popup' => [
                'page'        => '/file_management/testuploads',
                'page_action' => "Popup.load('/file_management/upload_test',800);",
            ],
            'planned.my_tests'            => '/test_takes/planned_teacher',
            'planned.my_tests.plan'       => [
                'page'        => '/test_takes/planned_teacher',
                'page_action' => "Popup.load('/test_takes/add',1000)",
            ],
            'planned.surveillance'        => '/test_takes/surveillance',
            'planned.assessment_open'     => '/test_takes/assessment_open_teacher',
            'taken.test_taken'            => '/test_takes/taken_teacher',
            'taken.normalize_test'        => '/test_takes/to_rate',
            'results.rated'               => '/test_takes/rated',
            'analyses.students'           => '/analyses/students_overview',
            'analyses.classes'            => '/analyses/school_classes_overview',
            'classes.my_classes'          => '/teacher_classes',
            'classes.my_schoollocation'   => '/teacher_classes/school_location_classes',
            'update-password'             => [
                'page'        => '/users/welcome',
                'page_action' => 'User.resetPassword();'
            ],
            'delay-auto-logout'           => [
                'page'        => '/users/welcome',
                'page_action' => "Popup.load('/users/prevent_logout?opened_by_user=true')",
            ],
            'chat'                        => [
                'page'        => '/users/welcome',
                'page_action' => "setTimeout(() => {openHubspotWidget(); Loading.hide()}, 500)",
            ],
            'knowledge_base'              => [
                'page'        => '/',
                'page_action' => "Popup.showExternalPage('https://support.test-correct.nl')",
            ],
            'webinar'                     => [
                'page'        => '/',
                'page_action' => "Popup.showExternalPage('https://embed.webinargeek.com/ac16aaa56a08d79ca2535196591dd91b20b70807849b5879fe', 600, 350)",
            ],
            'support_updates'             => [
                'page'        => '/',
                'page_action' => "Popup.showExternalPage('https://support.test-correct.nl/knowledge/wat-zijn-de-laatste-updates', 1000)",
            ],
        ];

        return $lookUpArray[$cakePage] ?? false;
    }

    protected function createCakeUrl($cakeRouteName)
    {
        $cakeAddress = $this->getCakeUrlString($cakeRouteName);

        $controller = new TemporaryLoginController();
        $request = new Request();

        if (!is_array($cakeAddress)) {
            $cakeAddress = [
                'page'        => '/',
                'page_action' => "Navigation.load('$cakeAddress')"
            ];
        }

        $request->merge([
            'options' => $cakeAddress,
        ]);

        return $controller->toCakeUrl($request);
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
            $menu->action->directive,
            $menu->action->method,
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
                'results',
            ],
            'tiles' => [
                'tests'   => ['my-uploads'],
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
    }
}
