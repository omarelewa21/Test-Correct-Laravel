<?php

namespace tcCore\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\Console\Output\ConsoleOutput;
use tcCore\Http\Helpers\BaseHelper;
use tcCore\Text2SpeechLog;
use tcCore\User;

class SwitchToNgrok extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ngrokSwitch:to {ngrok_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'switch to ngrok using id';

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
        try{
            $this->modifyEnvFile();
            $this->modifyConfigAppFile();
            $output->writeln('<info>switched to ngrok config</info>') ;
        }catch(\Exception $e){
            $output->writeln('<error>'.$e->getMessage().'</error>') ;
        }
    }

    private function modifyEnvFile()
    {
        $replaceArr = [
            'URL_LOGIN=http://testportal.test-correct.test/' => 'URL_LOGIN=http://'.$this->argument('ngrok_id'),
            'BASE_URL=http://testwelcome.test-correct.test/' => 'BASE_URL=http://'.$this->argument('ngrok_id'),
            'SHORTCODE_LINK=http://test-correct.test/inv/' => 'SHORTCODE_LINK=http://'.$this->argument('ngrok_id').'/inv/',
            'SHORTCODE_REDIRECT=http://test-correct.test/onboarding' => 'SHORTCODE_REDIRECT=http://'.$this->argument('ngrok_id').'/onboarding'
        ];
        $pathToEnvFile = base_path().'/.env';
        foreach ($replaceArr as $key => $value){
            $env = file_get_contents($pathToEnvFile);
            $instring = stristr($env,$key);
            if(!$instring){
                throw new \Exception('env value:'.$key.' not found');
            }
            $env = str_replace($key,$value,$env);
            file_put_contents($pathToEnvFile,$env);
        }
    }

    private function modifyConfigAppFile()
    {
        $pathConfigAppFile = config_path('app.php');
        $configApp = file_get_contents($pathConfigAppFile);
        $instring = stristr($configApp,'//ngrok_placeholder');
        if(!$instring){
            throw new \Exception('ngrok placeholder not found');
        }
        $configApp = str_replace('//ngrok_placeholder','app(\'url\')->forceRootUrl(\'http://'.$this->argument('ngrok_id').'\');',$configApp);
        file_put_contents($pathConfigAppFile,$configApp);
    }
}
