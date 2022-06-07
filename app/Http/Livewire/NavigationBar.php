<?php

namespace tcCore\Http\Livewire;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use tcCore\Http\Controllers\TemporaryLoginController;
use tcCore\Http\Helpers\NavigationBarHelper;

class NavigationBar extends Component
{
    public $activeRoute;

    public function mount()
    {
        $this->activeRoute = NavigationBarHelper::getActiveRoute();
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
            'dashboard'                 => '/users/welcome',
            'tests.test_bank'           => '/tests/index',
            'tests.question_bank'       => '/questions/index',
            'tests.my_uploads'          => '/file_management/testuploads',
            'planned.my_tests'          => '/test_takes/planned_teacher',
            'planned.surveillance'      => '/test_takes/surveillance',
            'planned.assessment_open'   => '/test_takes/assessment_open_teacher',
            'taken.test_taken'          => '/test_takes/taken_teacher',
            'taken.normalize_test'      => '/test_takes/to_rate',
            'results.rated'             => '/test_takes/rated',
            'analyses.students'         => '/analyses/students_overview',
            'analyses.classes'          => '/analyses/school_classes_overview',
            'classes.my_classes'        => '/teacher_classes',
            'classes.my_schoollocation' => '/teacher_classes/school_location_classes',
            'update-password'           => [
                'page'        => '/',
                'page_action' => 'User.resetPassword();'
            ],
            'delay-auto-logout'         => [
                'page'        => '/',
                'page_action' => "Popup.load('/users/prevent_logout?opened_by_user=true')",
            ],
            'chat'                      => [
                'page'        => '/',
                'page_action' => "openHubspotWidget()",
            ],
            'knowledge_base'            => [
                'page'        => '/',
                'page_action' => "Popup.showExternalPage('https://support.test-correct.nl')",
            ],
            'webinar'                   => [
                'page'        => '/',
                'page_action' => "Popup.showExternalPage('https://embed.webinargeek.com/ac16aaa56a08d79ca2535196591dd91b20b70807849b5879fe', 600, 350)",
            ],
            'support_updates' => [
                'page' => '/',
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
}
