<?php namespace tcCore\Http\Controllers;

use tcCore\MatchingQuestion;
use tcCore\OpenQuestion;
use tcCore\RankingQuestion;
use tcCore\Test;
use Illuminate\Support\Debug\Dumper;

class WelcomeController extends Controller {

	/*
	|--------------------------------------------------------------------------
	| Welcome Controller
	|--------------------------------------------------------------------------
	|
	| This controller renders the "marketing page" for the application and
	| is configured to only allow guests. Like most of the other sample
	| controllers, you are free to modify or remove it as you desire.
	|
	*/

	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->middleware('guest');
	}

	/**
	 * Show the application welcome screen to the user.
	 *
	 * @return Response
	 */
	public function index()
	{
		$test = Test::find(1);
		$question = new OpenQuestion();
		$this->ddWithoutDie($question);
		$test->questions()->save($question->parentInstance);
		$question->save();
		$this->ddWithoutDie($question);

		die();

		return view('welcome');
	}

	private function ddWithoutDie()
	{
		array_map(function($x) { (new Dumper)->dump($x); }, func_get_args());
	}
}
