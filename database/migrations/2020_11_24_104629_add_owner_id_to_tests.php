<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use tcCore\User;

class AddOwnerIdToTests extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tests', function (Blueprint $table) {
            $table->integer('owner_id')->nullable();
        });

        try {
            tcCore\Test::withTrashed()->chunkById(100, function ($tests) {
                collect($tests)->each(function (tcCore\Test $t) {
                    $user = User::withTrashed()->findOrFail($t->author_id);
                    $t->owner_id = $user->school_location_id;
                    $t->save();
                });
            });
        } catch(Throwable $e){
            dump($e->getMessage());
            $this->down();
            throw $e;
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tests', function (Blueprint $table) {
            $table->dropColumn(['owner_id']);
        });
    }
}
