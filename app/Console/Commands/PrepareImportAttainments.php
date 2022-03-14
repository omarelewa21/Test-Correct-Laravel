<?php

namespace tcCore\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use tcCore\Http\Controllers\AttainmentImportController;
use tcCore\User;

class PrepareImportAttainments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:prepare_attainments';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports attainments from an excel file on the server';

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
        $pathToFile = storage_path('app/attainments_upload/attainments.xlsx');
        $exists = file_exists($pathToFile);
        if(!$exists){
            $this->error('No file on the server!');
            exit;
        }
        $msg = 'Did you backup attainments table and question_attainments table?';
        if(!$this->confirm($msg)){
            exit;
        }
        $msg = 'file was created:'.date ("F d Y H:i:s.", filemtime($pathToFile)).'. Continue?';
        if(!$this->confirm($msg)){
            exit;
        }
        $this->loginAdmin();
        $request  = new Request();
        $params = [
            'session_hash' => Auth::user()->session_hash,
            'user'         => Auth::user()->username,
            'attainments' => $pathToFile,
        ];
        $request->merge($params);
        $response = (new AttainmentImportController())->setAttainmentsInactiveNotPresentInImport($request);
        if($response->getStatusCode()!=200){
            $this->error('something went wrong. msg:'.$response->getContent());
            exit;
        }
        $this->info($response->getContent());
        $response = (new AttainmentImportController())->removeSoftDeletedQuestionAttainments();
        if($response->getStatusCode()!=200){
            $this->error('something went wrong. msg:'.$response->getContent());
            exit;
        }
        $this->info($response->getContent());
        $this->logoutAdmin();
    }

    private function loginAdmin()
    {
        $user = User::whereHas(
            'roles', function($q){
            $q->where('name', 'Administrator');
        }
        )->first();
        Auth::login($user);
    }

    private function logoutAdmin()
    {
        Auth::logout();
    }
}
