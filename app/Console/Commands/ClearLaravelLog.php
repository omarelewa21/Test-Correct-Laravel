<?php

namespace tcCore\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ClearLaravelLog extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'log:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear the Laravel.log file';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if(Storage::disk('logs')->put('laravel.log', '')) {
            $this->info('The laravel.log file has been cleared succesfully.');
            return Command::SUCCESS;
        }
        $this->error('Something went wrong clearning the laravel.log file.');
        return Command::FAILURE;
    }
}
