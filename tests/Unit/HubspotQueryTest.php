<?php

namespace Tests\Unit;

use tcCore\LoginLog;
use tcCore\OnboardingWizardReport;
use tcCore\User;
use tcCore\Question;
use Tests\TestCase;
use Carbon\Carbon;

class HubspotQueryTest extends TestCase
{

    use \Illuminate\Foundation\Testing\DatabaseTransactions;
    
    protected function verbose_out($string) {

        fwrite(STDERR, print_r($string, TRUE));
    }
  
    /** @test */
    public function nr_of_questions_created_can_be_counted()
    {
        $nr_of_questions =  Question::whereDate('created_at', '>',Carbon::now()->subDays(7))->get()->count();
        
        $this->verbose_out($nr_of_questions);
    }
    
    /** @test */
    public function nr_of_test_takes_in_period_can_be_counted()
    {
        $nr_of_test_takes =  TestTakes::whereDate('created_at','>',Carbon::now()->subDays(200))->get()->count();
        
        var_dump( $nr_of_test_takes);
         
        exit();
    }
    
    
    /** @test */
    public function frits_test()
    {
        
        $this->verbose_out(OnboardingWizardReport::testClass());
        
        $this->verbose_out(OnboardingWizardReport::testFunction('getFirstTestTakenDate'));
        
        $nr_of_users =  User::get()->count();
        
        $this->verbose_out($nr_of_users);
        
    }
    
    
    
    
}