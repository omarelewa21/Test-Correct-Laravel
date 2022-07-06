<?php

namespace tcCore\Http\Livewire\Teacher;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use tcCore\Http\Controllers\TemporaryLoginController;
use tcCore\Test;

class TestsOverviewContextMenu extends Component
{
    public $displayMenu = false;

    public $btnId;

    public $openTab = 'personal';

    protected $listeners = [
        'showMenu',
    ];
    public $x;
    public $y;


    public function showMenu($args)
    {
        $this->test = Test::whereUuid($args['testUuid'])->first();
        $this->openTab = $args['openTab'];
        $this->btnId = sprintf('test%s', $args['id']);
        $this->displayMenu = true;
        $this->x = $args['x'];
        $this->y = $args['y'];
    }

    public function getTemporaryLoginToPdfForTest($testUuid)
    {
        $controller = new TemporaryLoginController();
        $request = new Request();
        $request->merge([
            'options' => [
                'page'        => sprintf('/tests/view/%s', $testUuid),
                'page_action' => sprintf("Loading.show();Popup.load('/tests/pdf_showPDFAttachment/%s', 1000);", $testUuid),
            ],
        ]);

        return $controller->toCakeUrl($request);
    }

    public function openEdit($testUuid)
    {
        $this->redirect(route('teacher.question-editor', [
            'testId'     => $testUuid,
            'action'     => 'edit',
            'owner'      => 'test',
            'withDrawer' => 'true',
            'referrer'   => 'teacher.tests',
        ]));
    }

    public function duplicateTest($testUuid)
    {
        // @TODO only duplicate when allowed?

        $test = Test::whereUuid($testUuid)->first();
        if ($test == null) {
            return 'Error no test was found';
        }

        if (!$test->canCopy(auth()->user())) {
            return 'Error duplication not allowed';
        }

        try {
            $newTest = $test->userDuplicate([], Auth::id());
        } catch (\Exception $e) {
            return 'Error duplication failed';
        }

        redirect()->to(route('teacher.test-detail', ['uuid' => $newTest->uuid]));

        return __('general.duplication successful');
    }

    public function render()
    {
        return view('livewire.teacher.tests-overview-context-menu');
    }
}
