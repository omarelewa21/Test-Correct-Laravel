<?php

namespace tcCore\Http\Helpers;

use Illuminate\Http\Request;
use tcCore\Http\Controllers\TemporaryLoginController;

class CakeRedirectHelper
{
    protected string $routeName;
    protected ?string $uuid = null;

    public static function redirectToCake(string $routeName, ?string $uuid = null) : void
    {
        $helper = new self($routeName, $uuid);

        redirect($helper->createCakeUrl());
    }

    public static function getCakeUrl(string $routeName, ?string $uuid = null) : string
    {
        $helper = new self($routeName, $uuid);

        return $helper->createCakeUrl();
    }

    protected function __construct(string $routeName, ?string $uuid = null)
    {
        $this->routeName = $routeName;
        $this->uuid = $uuid;
    }

    protected function getCakeUrlAndFollowupActionData()
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
                'page_action' => "Popup.showExternalPage('https://support.test-correct.nl/knowledge')",
            ],
            'webinar'                     => [
                'page'        => '/',
                'page_action' => "Popup.showExternalPage('https://embed.webinargeek.com/ac16aaa56a08d79ca2535196591dd91b20b70807849b5879fe', 600, 350)",
            ],
            'support_updates'             => [
                'page'        => '/',
                'page_action' => "Popup.showExternalPage('https://support.test-correct.nl/knowledge/wat-zijn-de-laatste-updates', 1000)",
            ],

            'school_location.new'    => [
                'page'        => '/',
                'page_action' => "Loading.show();Popup.load('/school_locations/add', 1100);",
            ],
            'school_location.view'   => [
                'page'        => '/',
                'page_action' => sprintf("Navigation.load('/school_locations/view/%s')", $this->uuid)
            ],
            'school_location.edit'   => [
                'page'        => sprintf("/school_locations/view/%s", $this->uuid),
                'page_action' => sprintf("Popup.load('/school_locations/edit/%s', 1100)", $this->uuid)
            ],
            'school_location.delete' => [
                'page'        => '/',
                'page_action' => "SchoolLocation.delete('$this->uuid', 0)"
            ],

            'school.new'    => [
                'page'        => '/',
                'page_action' => "Loading.show();Popup.load('/schools/add', 1100);",
            ],
            'school.view'   => [
                'page'        => '/',
                'page_action' => sprintf("Navigation.load('/schools/view/%s')", $this->uuid)
            ],
            'school.edit'   => [
                'page'        => sprintf("/schools/view/%s", $this->uuid),
                'page_action' => sprintf("Popup.load('/schools/edit/%s', 800)", $this->uuid)
            ],
            'school.delete' => [
                'page'        => '/',
                'page_action' => "School.delete('$this->uuid', 0)"
            ]
        ];

        return $lookUpArray[$this->routeName] ?? false;
    }

    protected function createCakeUrl() : string
    {
        $cakeRedirectData = $this->getCakeUrlAndFollowupActionData();

        $controller = new TemporaryLoginController();
        $request = new Request();

        if (!is_array($cakeRedirectData)) {
            $cakeRedirectData = [
                'page'        => '/',
                'page_action' => "Navigation.load('$cakeRedirectData')"
            ];
        }

        $request->merge([
            'options' => $cakeRedirectData,
        ]);

        return $controller->toCakeUrl($request);
    }
}