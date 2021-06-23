<?php

namespace tcCore\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use tcCore\Http\Helpers\ActingAsHelper;
use tcCore\Http\Helpers\ImportHelper;
use tcCore\Log;
use tcCore\SchoolLocation;
use tcCore\User;
use tcCore\UserRole;
use tcCore\UwlrSoapResult;

class UwlrProcessImport extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'uwlrimport:process {id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'process uwlr import data';

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
        $id = $this->argument('id');
        $resultSet = UwlrSoapResult::find($id);
        if(!$resultSet){
            $this->error('we could not find the corresponding resultset');
            return 1;
        }
        $accountManager = User::leftJoin('user_roles','user_roles.user_id','users.id')->where('user_roles.role_id',5)->first();
        Auth::loginUsingId($accountManager->getKey());
        $schoolLocation = SchoolLocation::where('external_main_code',$resultSet->brin_code)->where('external_sub_code',$resultSet->dependance_code)->orderBy('id','desc')->first();
        if($this->confirm('Are you sure you want to process the data for '.$schoolLocation->name.'?')){
            $helper = ImportHelper::initWithUwlrSoapResult(
                UwlrSoapResult::find($id),
                'sobit.nl'
            );

            $result = $helper->process();

            if (array_key_exists('errors', $result)) {
                if (!is_array($result['errors'])) {
                    $result['errors'] = [$result['errors']];
                }
                $this->alert(implode(PHP_EOL,$result['errors']));
            }
            if(count($result)) {
                $this->processingResult = collect($result)->join(PHP_EOL);
                $this->alert($this->processingResult);
            } else {
                $this->info('all done with no errors');
            }
            return 0;
        }
        return 0;
    }
}
