<?php namespace tcCore\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use tcCore\Lib\Repositories\PeriodRepository;
use tcCore\SchoolClass;
use tcCore\Test;
use tcCore\TestTake;
use tcCore\User;

class TestTakeCreateAndStart extends Command {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'testTake:createAndStart';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'create a test take and start';

    protected $defaultTeacherUsername = 'd1@test-correct.nl';
    protected $backupTeacherUsername = 'carloschoep+k999docent14@hotmail.com';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        $this->init();

        $teacher = $this->getTeacher();

        $test = $this->getTest();

        $useTestDirect = $this->confirm('Do you want to use TestDirect?',true);
        $addClass = true;
        if($useTestDirect){
            $addClass = $this->confirm('Do you want to add a class?',false);
        }
        $class = null;

        if($addClass){
            $class = $this->getClass(!$useTestDirect);
        }

        $useBrowserTesting = $this->confirm('Do you want to allow browser testing',true);

        Auth::loginUsingId($teacher->getKey());

        $testTake = new TestTake();
        $data = [
            'test_id' => $test->getKey(),
            'visible' => true,
            'date' => Carbon::today(),
            'weight' => 5,
            'test_take_status_id' => 1,
            'period_id' => PeriodRepository::getCurrentPeriod()->getKey(),
            'retake' => 0,
            'time_start' => Carbon::now(),
            'is_rtti_test_take' => 0,
            'allow_inbrowser_testing' => $useBrowserTesting,
            'guest_accounts' => $useTestDirect,
            'invigilators' => [$teacher->getKey()],
            'invigilator_note' => '',
            'test_kind_id' => 3
        ];
        if($class){
            $data['school_classes'] = [$class->getKey()];
        }
        $testTake->fill($data);
        $testTake->setAttribute('user_id', $teacher->getKey());

        if ($testTake->save() !== false) {
            $testTake->refresh();
            $testTake->fill(['test_take_status_id' => 3]);
            $testTake->save();
            if($testTake->save() !== false){
                $this->info('You\'re ready to go');
                return 0;
            }
        }
        $this->error("Sorry, the test take could not be created and started");
        return 0;

    }

    protected function getTest()
    {
        $testIdentifier = $this->ask('Which test do you want to use (id or name)?');
        if(is_numeric($testIdentifier)){
            $test = Test::find($testIdentifier);
        } else {
            $test = Test::whereName($testIdentifier)->where('is_system_test',false)->orderBy('created_at','desc')->first();
        }

        if(!$test){
            $this->error('Sorry, no test found using this identifier '.$testIdentifier);
            if($this->confirm('Do you want to try again?')){
                return $this->getTest();
            } else {
                $this->goQuit();
            }
        }
        return $test;
    }

    protected function getTeacher()
    {
        $teacherIdentifier = $this->ask('Which teacher do you want to create the testtake for (userid or username)?',$this->defaultTeacherUsername);
        if(is_numeric($teacherIdentifier)){
            $teacher = User::find($teacherIdentifier);
        } else {
            $teacher = User::whereUsername($teacherIdentifier)->first();
        }
        if(!$teacher){
            $this->error('Sorry, no teacher found using this identifier '.$teacherIdentifier);
            if($this->confirm('do you want to try again?')){
                return $this->getTeacher();
            } else {
                $this->goQuit();
            }
        }
        return $teacher;
    }

    protected function getClass($failOnNoClass = true)
    {
        $classIdentifier = $this->ask('Which class do you want to use (id or name)?');
        if(is_numeric($classIdentifier)){
            $class = SchoolClass::find($classIdentifier);
        } else {
            $class = SchoolClass::whereName($classIdentifier)->orderBy('created_at','desc')->first();
        }
        if(!$class){
            if($this->confirm('Do you want to try again to find the correct class?')){
                return $this->getClass($failOnNoClass);
            } else {
                if($failOnNoClass) {
                    $this->goQuit();
                }
            }
        }
        return $class;
    }

    protected function goQuit()
    {
        $this->info('okay, we quit');
        exit;
    }

    protected function init()
    {
        if(!User::whereUsername($this->defaultTeacherUsername)->count()){
            $this->defaultTeacherUsername = $this->backupTeacherUsername;
        }
    }
}
