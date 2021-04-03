<?php

namespace tcCore\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use tcCore\Test;
use tcCore\TestTake;

class TemporaryLoginController extends Controller
{
    public function teacherPreview(Test $test)
    {
        if (Auth::user()->isA('Teacher')) {
            return new RedirectResponse(url(route('test-preview', $test->uuid)));
        }
        abort(403);
    }

    public function studentPlayer(TestTake $test_take)
    {
        if (Auth::user()->isA('Student')) {
            return new RedirectResponse(url(route('student.test-take-laravel', $test_take->uuid)));
        }
        abort(403);
    }
}
