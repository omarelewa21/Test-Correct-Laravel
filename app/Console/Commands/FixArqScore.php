<?php namespace tcCore\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use tcCore\Lib\User\Factory;

class FixArqScore extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'fix:arqscore';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Fix the arq score to be set to the max instead of sum of the answers';

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle()
	{
        $list = \tcCore\MultipleChoiceQuestion::where('subtype','arq')->get();
        $adjusted = 0;
        if(app()->runningInConsole()) {
            $bar = $this->output->createProgressBar(count($list));
            $bar->start();
        }
        $list->each(function(\tcCore\MultipleChoiceQuestion $m) use ($bar, &$adjusted){
            if(app()->runningInConsole()) {
                $bar->advance();
            }
            $q = $m->getQuestionInstance();
            if($q) {
                $q->score = (int)$m->multipleChoiceQuestionAnswers->max('score');
                if ($q->isDirty('score') && $q->save()) {
                    $adjusted++;
                }
            }
        });
        if(app()->runningInConsole()){
            $bar->finish();
            $this->info(PHP_EOL.'A total of '.$adjusted.' ARQ questions have been adjusted');
        }

	}


}
