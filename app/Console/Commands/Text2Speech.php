<?php

namespace tcCore\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\Console\Output\ConsoleOutput;
use tcCore\Http\Helpers\BaseHelper;
use tcCore\Text2SpeechLog;
use tcCore\User;

class Text2Speech extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'text2speech:provision {user_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Adds text2speech to user, dev utility only';

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
            $userId = $this->argument('user_id');
            $user = User::findOrFail($userId);
            if($user->text2speech){
                $output->writeln('<info>text2speech already provisioned for:'.$user->getFullNameWithAbbreviatedFirstName().'</info>') ;
                return;
            }
            $user->text2speech = true;
            $user->save();
            \tcCore\Text2Speech::create([
                'user_id'    => $user->getKey(),
                'active'     => true,
                'acceptedby' => $user->getKey(),
                'price'      => config('custom.text2speech.price')
            ]);
            Text2SpeechLog::create([
                'user_id' => $user->getKey(),
                'action'  => 'ACCEPTED',
                'who'     => $user->getKey()
            ]);
            $output->writeln('<info>text2speech added to '.$user->getFullNameWithAbbreviatedFirstName().'</info>') ;
        }catch(\Exception $e){
            $output->writeln('<error>'.$e->getMessage().'</error>') ;
        }
    }
}
