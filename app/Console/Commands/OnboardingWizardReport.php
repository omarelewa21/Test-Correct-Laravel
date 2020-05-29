<?php

namespace tcCore\Console\Commands;

use Illuminate\Console\Command;

class OnboardingWizardReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'onboarding_wizard_report:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates the onboarding wizard report';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        \tcCore\OnboardingWizardReport::updateForAllTeachers();
        //
    }
}
