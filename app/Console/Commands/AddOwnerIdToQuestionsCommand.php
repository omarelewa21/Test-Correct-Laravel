<?php

namespace tcCore\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use tcCore\Question;

class AddOwnerIdToQuestionsCommand extends Command
{
    protected $signature = 'questions:add_owner_id';

    protected $description = 'Fill the owner_id column for questions which do not already have one.';

    public function handle()
    {
        DB::beginTransaction();
        try {
            Question::addOwnerIds();

            DB::commit();
        } catch (\Throwable $e) {
            Log::error($e);
            DB::rollBack();
        }
    }
}
