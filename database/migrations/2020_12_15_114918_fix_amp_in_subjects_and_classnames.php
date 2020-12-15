<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use tcCore\Http\Requests\Request;
use tcCore\SchoolClass;

class FixAmpInSubjectsAndClassnames extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        SchoolClass::where('name','like','%&%')->where('name','not like','%&amp;%')->get()->each(function(SchoolClass $sc){
            $name = $sc->name;
            Request::filter($name);
            $sc->name = $name;
            $sc->save();
        });

        \tcCore\Subject::where('name','like','%&%')->where('name','not like','%&amp;%')->get()->each(function(\tcCore\Subject $s){
            $name = $s->name;
            Request::filter($name);
            $s->name = $name;
            $s->save();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

    }
}
