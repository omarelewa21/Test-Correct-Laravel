<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SetDataToNewColumsnForFileManagements extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \tcCore\FileManagement::whereNull('parent_id')->get()->each(function (\tcCore\FileManagement $f) {
            $f->update([
                'class' => $f->type === 'classupload' ? $f->typedetails->class : null,
                'subject' => $f->typedetails->subject ?: null,
                'education_level_year' => $f->typedetails->education_level_year ?: 0,
                'education_level_id' => $f->typedetails->education_level_id ?: 0,
                'test_kind_id' => $f->type === 'testupload' ? $f->typedetails->test_kind_id : 0,
                'test_name' => $f->type === 'testupload' ? $f->typedetails->name : null,
            ]);
        });
        $fileNames = [];
        $lastParentId = null;
        \tcCore\FileManagement::whereNotNull('parent_id')->orderBy('parent_id')->get()->each(function(\tcCore\FileManagement $f) use (&$fileNames, &$lastParentId){
            if(null !== $lastParentId && $lastParentId != $f->parent_id){
                $fm = \tcCore\FileManagement::withTrashed()->find($lastParentId);
                if(null !== $fm) {
                    $fm->update(['orig_filenames' => sprintf("|%s|",implode('|', $fileNames))]);
                }
                $fileNames = [];
            }
            $fileNames[] = $f->origname;
            $lastParentId = $f->parent_id;
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \tcCore\FileManagement::whereNull('parent_id')->get()->each(function(\tcCore\FileManagement $f){
            $f->typedetails->education_level_id = $f->education_level_id;
            $f->typedetails->education_level_year = $f->education_level_year;
            $f->typedetails->subject = $f->subject;
            $f->typedetails->test_kind_id = $f->test_kind_id;
            if($f->type === 'testupload') {
                $f->typedetails->name = $f->test_name;
                $f->typedetails->test_kind_id = $f->test_kind_id;
            } else {
                $f->typedetails->class = $f->class;
            }

            $f->update([
                'typedetails' => $f->typedetails,
            ]);
        });
    }
}
