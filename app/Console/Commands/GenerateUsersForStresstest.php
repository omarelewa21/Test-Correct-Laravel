<?php

namespace tcCore\Console\Commands;

use Illuminate\Console\Command;
use tcCore\Log;
use tcCore\SchoolClass;
use tcCore\SchoolLocation;
use tcCore\SchoolLocationSchoolYear;
use tcCore\Student;
use tcCore\Teacher;
use tcCore\User;
use tcCore\UserRole;

class GenerateUsersForStresstest extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stresstest:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a list of users in their own school location';

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
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        $schoolLocationName = 'stresstest';


        $schoolLocation = SchoolLocation::where('name','like','stresstest%')->orderBy('created_at','DESC')->first();

        if($schoolLocation){
            $id = (int) str_replace($schoolLocationName,'',$schoolLocation->name)+0;
            $id++;
        }
        else{
            $id = 1;
        }
        $schoolLocationName = $schoolLocationName . $id;

        $userCount = $this->ask('How many users do you want to be created?');
        $password = $this->ask('what password do you want to use for these users?');

        $this->line('We\'re going to create the data, this may take a while...');
        $total = $userCount+1+1+1+1; // usercount + schoolLocation + schoolClass + schoolLocationSchoolyear + teacher
        $bar = $this->output->createProgressBar($total);


        $schoolLocation = SchoolLocation::create([
           'user_id' => 520,
           'grading_scale_id' => 1,
           'customer_code' => $schoolLocationName,
           'name' => $schoolLocationName,
           'main_address' => 1,
           'main_postal' => 1,
           'main_city' => 1,
           'main_country' => 1,
           'invoice_address' => 1,
            'invoice_postal' => 1,
            'invoice_city' => 1,
            'invoice_country' => 1,
            'visit_address' => 1,
            'visit_postal' => 1,
            'visit_city' => 1,
            'visit_country' => 1,
            'is_rtti_school_location' => 0
        ]);

        $bar->advance();

        $schoolClass = SchoolClass::create([
           'school_location_id' => $schoolLocation->getKey(),
           'education_level_id' => 12,
            'school_year_id' => 27,
            'name' => sprintf('%s klas',$schoolLocationName),
            'education_level_year' => 2,
            'is_main_school_class' => 1,
            'do_not_overwrite_from_interface' => 0,
        ]);

        $bar->advance();

        SchoolLocationSchoolYear::create([
           'school_location_id' => $schoolLocation->getKey(),
           'school_year_id' => 27,
        ]);

        $bar->advance();

        for($i=1; $i <= $userCount; $i++){
            $this->createStudent($password,$schoolLocation,$schoolClass,$i);
            $bar->advance();
        }

        $this->createTeacher($password,$schoolLocation,$schoolClass);
        $bar->advance();

        $this->info('');
        $this->info(sprintf('We\'ve created <options=bold;fg=white>%d</> new students for school location <options=bold;fg=white>%s</>',$userCount,$schoolLocationName));
        $this->info(sprintf('the password for every student is <options=bold;fg=white>%s</>',$password));
        $this->info(sprintf('The usernames are as follows <options=bold;fg=white>info+%s-1@test-correct.nl</> ... <options=bold;fg=white>info+%s-%d@test-correct.nl</>',$schoolLocationName, $schoolLocationName,$userCount));
        $this->info(sprintf('there\'s one teacher created with the username <options=bold;fg=white>info+%s-teacher@test-correct.nl</> and the same password as the others',$schoolLocationName));

    }

    protected function createStudent($password,$schoolLocation,$schoolClass,$nr)
    {
        $user = User::create([
           'school_location_id' => $schoolLocation->getKey(),
           'username' => sprintf('info+%s-%d@test-correct.nl',$schoolLocation->name,$nr),
            'password' => $password,
            'name_first' => $schoolLocation->name,
            'name' => sprintf('student-%d',$nr),
            'api_key' => str_random(40),
            'send_welcome_email' => 1
        ]);

        if(!$user){
            throw new \Exception('could not create student');
        }

        UserRole::create([
            'user_id'=> $user->getKey(),
            'role_id' => 3
        ]);

        Student::create([
            'user_id' => $user->getKey(),
            'class_id' => $schoolClass->getKey(),
        ]);

        return $user;
    }

    protected function createTeacher($password,$schoolLocation,$schoolClass)
    {
        $user = User::create([
            'school_location_id' => $schoolLocation->getKey(),
            'username' => sprintf('info+%s-teacher@test-correct.nl',$schoolLocation->name),
            'password' => $password,
            'name_first' => $schoolLocation->name,
            'name' => sprintf('teacher'),
            'api_key' => str_random(40),
            'send_welcome_email' => 1
        ]);

        if(!$user){
            throw new \Exception('could not create teacher');
        }

        UserRole::create([
            'user_id'=> $user->getKey(),
            'role_id' => 1
        ]);

        Teacher::create([
            'user_id' => $user->getKey(),
            'class_id' => $schoolClass->getKey(),
            'subject_id' => 76
        ]);

        return $user;
    }
}
