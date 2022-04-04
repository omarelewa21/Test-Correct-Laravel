<?php

namespace tcCore\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\Console\Output\ConsoleOutput;
use tcCore\Http\Helpers\ActingAsHelper;
use tcCore\Http\Helpers\BaseHelper;
use tcCore\User;
use tcCore\Traits\Dev\CompletionQuestionTrait;
use tcCore\Traits\Dev\DrawingQuestionTrait;
use tcCore\Traits\Dev\MatchingQuestionTrait;
use tcCore\Traits\Dev\MultipleChoiceQuestionTrait;
use tcCore\Traits\Dev\OpenQuestionTrait;
use tcCore\Traits\Dev\RankingQuestionTrait;
use tcCore\Traits\Dev\TestTrait;
use Illuminate\Support\Facades\Http;


class SetupTestsCommand extends Command
{
    use TestTrait;
    use MultipleChoiceQuestionTrait;
    use CompletionQuestionTrait;
    use DrawingQuestionTrait;
    use MatchingQuestionTrait;
    use OpenQuestionTrait;
    use RankingQuestionTrait;


    private $testId;
    private static $user;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'setup_tests:scaffold  {user_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
     * @return int
     */
    public function handle()
    {
        $output = new ConsoleOutput();
        try {
            if (!BaseHelper::notProduction()) {
                throw new \Exception('not in production');
            }
            $this->loginUser();
            $this->createTest();
            $attributes = $this->getAttributesForMultipleChoiceQuestion($this->testId);
            $this->createQuestion($attributes);
            $attributes = $this->getOpenQuestionAttributes(['test_id' => $this->testId]);
            $this->createQuestion($attributes);
            $attributes = $this->getOpenQuestionAttributes(['test_id' => $this->testId,'subtype'=>'long','question'=>'lange open vraag']);
            $this->createQuestion($attributes);
            $attributes = $this->getCompletionQuestionAttributes(['test_id' => $this->testId]);
            $this->createQuestion($attributes);
            $attributes = $this->getCompletionQuestionSelectionAttributes(['test_id' => $this->testId]);
            $this->createQuestion($attributes);
            $attributes = $this->getAttributesForDrawingQuestion($this->testId);
            $this->createQuestion($attributes);
            $attributes = $this->getAttributesForMatchingQuestion($this->testId);
            $this->createQuestion($attributes);
            $attributes = $this->getAttributesForClassifyQuestion($this->testId);
            $this->createQuestion($attributes);
            $attributes = $this->getAttributesForRankingQuestion($this->testId);
            $this->createQuestion($attributes);
            $output->writeln('<info>test provisioned</info>') ;
        }catch(\Exception $e){
            $output->writeln('<error>'.$e->getMessage().'</error>') ;
        }
    }

    private function loginUser():void
    {
        $userId = $this->argument('user_id');
        $user = User::findOrFail($userId);
        self::$user = $user;
        if(!$user->isA('Teacher')){
            throw new \Exception('user is not a teacher');
        }
        Auth::login($user);
        ActingAsHelper::getInstance()->setUser($user);
    }

    private function createTest()
    {
        $attributes = $this->getAttributesForTest();
        unset($attributes['school_classes']);
        $this->createTLCTest($attributes);
    }



    private function getAttributesForTest(){

        return $this->getTestAttributes([
            'name'                   => 'Toets met alle vragen '.Carbon::now()->format('d-m-Y H:i:s'),
            'abbreviation'           => 'TAV',
            'subject_id'             => '1',
            'introduction'           => 'intro',
            'education_level_year'   => 2,
            'education_level_id'   => 1
        ]);

    }

    private function createTLCTest($attributes){
        $response = $this->post(
            'api-c/test',
            self::getUserAuthRequestData(self::$user,$attributes)
        );
        $testId = json_decode($response->body(),true)['id'];
        $this->testId = $testId;
    }

    private static function getUserAuthRequestData($user,$overrides)
    {
        return array_merge([
            'session_hash' => $user->session_hash,
            'user'         => $user->username,
        ], $overrides);
    }

    private function post($url,$attributes)
    {
//        $url = config('app.base_url').'/'.$url;
        $url = 'http://test-correct.test/'.$url;
        $response =  Http::post($url,$attributes);
        return $response;
    }

    private function createQuestion($attributes)
    {
        $response =  $this->post(
            'api-c/test_question',
            static::getTeacherOneAuthRequestData(
                $attributes
            )
        );
        return $response;
    }

    private static function getTeacherOneAuthRequestData($overrides = [])
    {
        return self::getUserAuthRequestData(
            self::$user,
            $overrides
        );
    }
}