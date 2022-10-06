<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use tcCore\Http\Livewire\Student\Header;
use tcCore\TemporaryLogin;
use tcCore\User;
use tcCore\Test;
use tcCore\TestQuestion;
use tcCore\Question;
use tcCore\GroupQuestion;
use tcCore\DrawingQuestion;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use tcCore\Traits\Dev\TestTrait;
use tcCore\Traits\Dev\DrawingQuestionTrait;
use tcCore\Traits\Dev\MultipleChoiceQuestionTrait;
use tcCore\Traits\Dev\GroupQuestionTrait;
use Illuminate\Support\Facades\DB;

class AnalysesToggleTabTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function when_no_allow_analyses_setting_is_present_for_school_location_it_should_redirect_to_cake()
    {
        $schoolLocation = $this->getStudentOne()->schoolLocation;
        $schoolLocation->allow_analyses = false;

        $this->actingAs($this->getStudentOne());
        Livewire::test(Header::class)
            ->call('analyses')
            ->assertRedirect(sprintf('http://testportal.test-correct.test/users/temporary_login/%s', TemporaryLogin::latest()->first()->uuid));
    }

    /** @test */
    public function when_allow_analyses_setting_is_present_for_school_location_it_should_link_student_analyses_route()
    {
        $schoolLocation = $this->getStudentOne()->schoolLocation;
        $schoolLocation->allow_analyses = true;
        $this->actingAs($this->getStudentOne());

        Livewire::test(Header::class)
            ->call('analyses')
            ->assertRedirect(route('student.analyses.show'));


    }
}

