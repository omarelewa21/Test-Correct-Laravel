<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use tcCore\Question;

class AddOwnerIdToQuestionsTable extends Migration
{
    public function up()
    {
        DB::beginTransaction();
        try {
            Schema::table('questions', function (Blueprint $table) {
                $table->unsignedInteger('owner_id')->after('score')->nullable();
            });

//            Question::addOwnerIdToAllQuestions();;

            DB::commit();
        } catch (Throwable $e) {
            Log::error($e);

            DB::rollBack();
        }
    }

    public function down()
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn('owner_id');
        });
    }
}