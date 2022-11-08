<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateTestAuthorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        try {
            Schema::create('test_authors', function (Blueprint $table) {
                $table->integer('test_id');
                $table->integer('user_id');
                $table->timestamps();
                $table->softDeletes();
                $table->primary(['test_id','user_id']);
            });

            \tcCore\Test::withTrashed()->get()->each(function (\tcCore\Test $test) {
                \tcCore\TestAuthor::create([
                    'user_id' => $test->author_id,
                    'test_id' => $test->getKey()
                ]);
            });
        } catch (Throwable $e){
            Throw $e;
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('test_authors');
    }
}
