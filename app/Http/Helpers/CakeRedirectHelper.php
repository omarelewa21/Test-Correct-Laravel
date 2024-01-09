<?php

namespace tcCore\Http\Helpers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use tcCore\Exceptions\CleanRedirectException;
use tcCore\Exceptions\RedirectException;
use tcCore\Http\Controllers\TemporaryLoginController;

class CakeRedirectHelper
{
    protected function __construct(
        protected string  $routeName,
        protected ?string $uuid = null,
        protected ?int $pageNumber = null,
        protected ?string $returnRoute = null,
        protected ?array $notification = null,
    ) {
        $this->validateNotification($notification);
    }

    public static function redirectToCake(
        string $routeName = 'dashboard',
        ?string $uuid = null,
        ?int $pageNumber = null,
        ?string $returnRoute = null,
        ?array $notification = null,
    ) {
        $helper = new self($routeName, $uuid, $pageNumber, $returnRoute, $notification);

        return redirect($helper->createCakeUrl());
    }

    public static function getCakeUrl(string $routeName, ?string $uuid = null, ?int $pageNumber = null, ?string $returnRoute = null): string
    {
        $helper = new self($routeName, $uuid, $pageNumber, $returnRoute);

        return $helper->createCakeUrl();
    }

    protected function getCakeUrlAndFollowupActionData()
    {
        $lookUpArray = $this->getLookupArray();

        return $lookUpArray[$this->routeName] ?? false;
    }

    protected function createCakeUrl(): string
    {
        $cakeRedirectData = $this->getCakeUrlAndFollowupActionData();

        $controller = new TemporaryLoginController();
        $request = new Request();

        if (!is_array($cakeRedirectData)) {
            $cakeRedirectData = [
                'page'        => '/',
                'page_action' => "Navigation.load('$cakeRedirectData')",
            ];
        }

        if ($this->notification) {
            $cakeRedirectData = $this->addNotificationToPageAction($cakeRedirectData);
        }

        if (session()->has('support')){
            $cakeRedirectData = array_merge($cakeRedirectData,
                [
                    'support' => [
                        'id'=> session()->get('support.id'),
                        'name'=> session()->get('support.name'),
                        ]
                ]
            );

            session()->forget('support');
        }

        $request->merge([
            'options' => array_merge(
                            $cakeRedirectData,
                            ["return_route" => $this->returnRoute ?? url()->previous()]
                        )
        ]);

        return $controller->toCakeUrl($request);
    }

    public static function getRouteNameByUrl(string $url, ?string $uuid = null)
    {
        $helper = new self($url, $uuid);

        return collect($helper->getLookupArray())
            ->filter(function ($value) use ($url) {
                if (is_array($value)) {
                    return collect($value)->first(function ($subValue) use ($url) {
                        return str($subValue)->contains($url);
                    });
                }
                return str($url)->contains($value);

            })
            ->keys()
            ->first();
    }

    /**
     * @return array
     */
    private function getLookupArray(): array
    {
        return [
            'dashboard'                   => '/users/welcome',
            'tests.test_bank'             => '/tests/index',
            'tests.question_bank'         => '/questions/index',
            'tests.my_uploads'            => '/file_management/testuploads',
            'tests.my_uploads_with_popup' => [
                'page'        => '/file_management/testuploads',
                'page_action' => "Popup.load('/file_management/upload_test',800);",
            ],
            'test_takes.view'             => sprintf('/test_takes/view/%s', $this->uuid),
            'test_takes.discussion'       => sprintf('/test_takes/discussion/%s', $this->uuid),
            'planned.my_tests'            => '/test_takes/planned_teacher',
            'planned.my_tests.plan'       => [
                'page'        => '/test_takes/planned_teacher',
                'page_action' => "Popup.load('/test_takes/add',1000)",
            ],
            'planned.surveillance'        => '/test_takes/surveillance',
            'planned.assignment_open'     => '/test_takes/assignment_open_teacher',
            'taken.test_taken'            => '/test_takes/taken_teacher',
            'taken.normalize_test'        => '/test_takes/to_rate',
            'taken.schedule_makeup'       => sprintf('/test_takes/add_retake/%s', $this->uuid),
            'taken.rate_participant'      => sprintf('/test_takes/rate_teacher_participant/%s', $this->uuid),
            'taken.normalize'             => sprintf('/test_takes/normalization/%s', $this->uuid),
            'taken.marking'               => sprintf('/test_takes/set_final_rates/%s', $this->uuid),
            'results.rated'               => '/test_takes/rated',
            'analyses.teacher'            => sprintf('/teacher_analyses/view/%s', $this->uuid),
            'analyses.classes'            => '/analyses/school_classes_overview',
            'new_analyses.classes'        => '/teacher_analyses',
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
                'page_action' => sprintf("Popup.showExternalPage('%s')", config('app.knowlegde_bank_url')),
            ],
            'webinar'                     => [
                'page'        => '/',
                'page_action' => "Popup.showExternalPage('https://embed.webinargeek.com/ac16aaa56a08d79ca2535196591dd91b20b70807849b5879fe', 600, 350)",
            ],
            'support_updates'             => [
                'page'        => '/',
                'page_action' => sprintf("Popup.showExternalPage('%s/wat-zijn-de-laatste-updates', 1000)", config('app.knowlegde_bank_url')),
            ],

            'school_location.new'    => [
                'page'        => '/',
                'page_action' => "Loading.show();Popup.load('/school_locations/add', 1100);",
            ],
            'school_location.view'   => [
                'page'        => '/',
                'page_number' => $this->pageNumber,
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
                'page_number' => $this->pageNumber,
                'page_action' => sprintf("Navigation.load('/schools/view/%s')", $this->uuid)
            ],
            'school.edit'   => [
                'page'        => sprintf("/schools/view/%s", $this->uuid),
                'page_action' => sprintf("Popup.load('/schools/edit/%s', 800)", $this->uuid)
            ],
            'school.delete' => [
                'page'        => '/',
                'page_action' => "School.delete('$this->uuid', 0)"
            ],

            'files.class_uploads'                 => '/file_management/classuploads',
            'files.view_testupload' => sprintf('/file_management/view_testupload/%s', $this->uuid),

            'reports.marketing'       => [
                'page'        => '/users/welcome',
                'page_action' => 'window.location.href = "/users/marketing_report"',
            ],
            'reports.school_location' => [
                'page'        => '/users/welcome',
                'page_action' => 'window.location.href = "/users/school_location_report"',
            ],

            'database.umbrella_organizations' => '/umbrella_organisations',
            'database.attainments_import'     => '/attainments',
            'database.attainmentscito_import' => '/attainments_cito',

            'qtiimport.index'      => '/qtiimport/index',
            'qtiimport_cito'       => '/qtiimport_cito',
            'qtiimport_batch_cito' => '/qtiimport_batch_cito',

            'infos.index' => '/infos/index',
            'messages' => '/messages'
        ];
    }

    /**
     * @param array|null $notification
     * @return void
     * @throws \Exception
     */
    private function validateNotification(?array $notification): void
    {
        if ($notification) {
            if (!array_key_exists('message', $this->notification)) {
                throw new \Exception('Notifications should always have a message');
            }
        }
    }

    /**
     * @param mixed $cakeRedirectData
     * @return mixed
     */
    private function addNotificationToPageAction(mixed $cakeRedirectData): mixed
    {
        $cakeRedirectData['page_action'] = sprintf(
            "%s;Notify.notify('%s', '%s');",
            $cakeRedirectData['page_action'],
            $this->notification['message'],
            $this->notification['type'] ?? 'info'
        );
        return $cakeRedirectData;
    }
}
