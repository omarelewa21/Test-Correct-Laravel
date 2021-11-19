<?php namespace tcCore\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use tcCore\Http\Helpers\UserHelper;
use tcCore\Jobs\CountTeacherQuestions;
use tcCore\Lib\User\Factory;
use tcCore\User;

class BitLoginMonitor extends Command {

    CONST TEACHER_USERNAME = 'bit-teacher@test-correct.nl';
    CONST STUDENT_USERNAME = 'bit-student@test-correct.nl';

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'bit:monitor {--t|type=teacherlogin}';

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

        $type = $this->option('type');

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
	}

    protected function handleTeacherLogin()
    {
        $user = User::where('username',self::TEACHER_USERNAME)->first();
        (new UserHelper())->handleAfterLoginValidation($user,false, false);
        // also try to add something to the jobs table as that failed before
        dispatch(new CountTeacherQuestions($user));
    }

    protected function handleStudentLogin()
    {
        $user = $this->handleLogin(self::STUDENT_USERNAME)
    }

    protected function handleLogin($username)
    {
        $user = User::where('username',$username)->first();
        (new UserHelper())->handleAfterLoginValidation($user,false, false);
        return $user;
    }
}
