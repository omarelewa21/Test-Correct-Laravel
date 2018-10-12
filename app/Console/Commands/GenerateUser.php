<?php namespace tcCore\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use tcCore\Lib\User\Factory;

class GenerateUser extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'generate:user';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Generates a user with the given username and password.';

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
	public function fire()
	{
		$username = $this->argument('username');
		$password = $this->argument('password');

		$data = [
			'username' => $username,
			'password' => $password
		];

		if(($user = $this->factory->generate($data)) !== false){
			$this->info("User successfully generated! You're API key: " . $user->apiKey());
		} else {
			$this->error("User could not be saved.");
		}
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return [
			['username', InputArgument::REQUIRED, 'Example: test@domein.nl'],
			['password', InputArgument::REQUIRED, 'Example: MijnTest'],
		];
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return [];
	}

}
