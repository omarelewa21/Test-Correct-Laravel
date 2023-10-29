<?php

namespace tcCore\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use tcCore\FeatureSetting;
use tcCore\SchoolLocation;
use tcCore\Test;
use tcCore\User;

class MassPublishTests extends Command
{
    protected int $currentLineLength = 0;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:massPublish';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish tests for a specific abbreviation from toetsenbakker to actual right user';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        ini_set('memory_limit', '-1');

        if(!$schoolLocationId = $this->ask('Which school location id are the tests in?')){
            $this->error('please let us which school location id to look for');
            return Command::FAILURE;
        }

        if(!$abbrFrom = $this->ask('Which abbreviation do the tests have now?')){
            $this->error('please let us which abbreviation to look for');
            return Command::FAILURE;
        }

        $users = User::whereIn('id',Test::where('owner_id',$schoolLocationId)->where('abbreviation',$abbrFrom)->select('author_id'))->pluck('username','id');

        if($users->count() < 1){
            $this->error('Are you sure you entered the right details, we could not find any authors for these details');
            return Command::FAILURE;
        }

        if(!$authorUsername = $this->choice('Which user to use?',array_values($users->toArray()),0)){
            $this->error('We need a user to work with');
            return Command::FAILURE;
        }

        $authorId = $users->where(function($username, $id) use ($authorUsername){
            return $username == $authorUsername;
        })->map(function($username,$id){
            return $id;
        })->first();

        if(!$authorId){
            $this->error('The user could not be found');
            return Command::FAILURE;
        }

        if(!$abbrTo = $this->ask('What abbreviation to set the test to eventually?',Str::upper($abbrFrom))){
            $this->error('Sorry but we need an abbreviation to set the test to');
            return Command::FAILURE;
        }

        if($abbrTo !== Str::upper($abbrTo)){
            if($this->confirm('Do you want to use the capitalized version of your abbreviation ('.Str::upper($abbrTo).')?',true)){
                $abbrTo = Str::upper($abbrTo);
            }
        }

        $testsQueryBuilder = Test::where('abbreviation',$abbrFrom)->where('owner_id',$schoolLocationId)->where('author_id',$authorId);
        $testCount = $testsQueryBuilder->count();
        if(!$testCount){
            $this->error('No tests found to change');
            return Command::FAILURE;
        };


        $abbrInBetween = null;
        if($abbrFrom === $abbrTo){
            $abbrInBetween = date('YmdHis');
            $confirmText = sprintf('We are going to get the %d tests change the abbreviation towards %s and then save again towards %s in order to publish them, is that okay?', $testCount, $abbrInBetween,$abbrTo);
        } else {
            $confirmText = sprintf('We are going to get the %d tests change the abbreviation towards %s and save again in order to publish them, is that okay?', $testCount, $abbrTo);
        }

        $this->table(
            ['id', 'abbreviation','author_id','name'],
            $testsQueryBuilder->get(['id','abbreviation','author_id','name'])->toArray(),
        );

        if(!$this->confirm($confirmText)){
            return Command::FAILURE;
        }

        Auth::loginUsingId($authorId);

        $this->updateTests($testsQueryBuilder, $testCount, $abbrTo, $abbrInBetween);

        return Command::SUCCESS;
    }

    protected function updateTests($testsQueryBuilder, $testCount, $abbrTo, $abbrInBetween = null)
    {
        $nr = 1;
        $testsQueryBuilder->chunkById(50, function($chunk) use (&$nr, $testCount, $abbrTo, $abbrInBetween){
            $chunk->each(function(Test $test) use (&$nr, $testCount, $abbrTo, $abbrInBetween){
                $this->writeInfoText(sprintf('%d/%d: Test %s in progress...', $nr, $testCount, $test->name));
                $test->abbreviation = $abbrInBetween ?? $abbrTo;
                $test->save();
                if($abbrInBetween){
                    $test->abbreviation = $abbrTo;
                    $test->save();
                }
                $this->writeDoneInfo();
                $nr++;
           });
        });

        $this->writeInfoText('All done');
        $this->newLine();
    }

    protected function writeInfoText($text, $endWithLineBreak = false)
    {
        $this->output->write('<info>'.$text.'<info>',$endWithLineBreak);
        $this->currentLineLength = strlen($text);
    }

    protected function writeDoneInfo($text = 'done', $endOnPosition = 100, $withMemoryUsage = true)
    {
        $lastLength = $this->currentLineLength;
        if($endOnPosition){
            $extraDots = $endOnPosition - strlen($text) - $lastLength;
            for($i=0;$i < $extraDots; $i++){
                $text = '.'.$text;
            }
        }

        $size = memory_get_usage(true);
        $unit = array('b', 'kb', 'mb', 'gb', 'tb', 'pb');
        $usage = @round($size / pow(1024, ($i = floor(log($size, 1024)))), 2).' '.$unit[$i];
        $text .= ' ['.$usage.']';

        $this->writeInfoText($text,true);
    }

}
