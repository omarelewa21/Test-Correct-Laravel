<?php namespace tcCore\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use tcCore\Http\Helpers\ActingAsHelper;
use tcCore\Http\Helpers\UserHelper;
use tcCore\Jobs\CountTeacherQuestions;
use tcCore\Lib\User\Factory;
use tcCore\User;

class BitLoginMonitor extends Command {

    CONST TEACHER_USERNAME = 'testing+bitteacher@test-correct.nl';
    CONST STUDENT_USERNAME = 'testing+bitstudent@test-correct.nl';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bit:monitor {type=teacherlogin}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Monitoring for BIT';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct(Factory $factory)
	{
		$this->factory = $factory;

		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle()
	{

        $type = $this->argument('type');

        switch($type){
            case 'teacherlogin':
                $this->handleTeacherLogin();
                break;
            case 'studentlogin':
                $this->handleStudentLogin();
                break;
            default:
                $this->handleDefault();
                break;
        }
        echo "OK";
        return 0;
	}

    protected function handleDefault()
    {
        echo "NOTHING TO DO";
        exit;
    }

    protected function handleTeacherLogin()
    {
        $user =$this->handleLogin(self::TEACHER_USERNAME);
        // also try to add something to the jobs table as that failed before
        dispatch(new CountTeacherQuestions($user));
    }

    protected function handleStudentLogin()
    {
        $this->handleLogin(self::STUDENT_USERNAME);
    }

    protected function handleLogin($username)
    {
        $user = User::where('username',$username)->first();
        if(!$user){
            echo "NO USER FOUND";
            exit;
        }
        ActingAsHelper::getInstance()->setUser($user);
        (new UserHelper())->handleAfterLoginValidation($user,false, false);
        return $user;
    }
}
